<?php

namespace App\Services;

use App\Models\ReferralClick;
use App\Models\SellerReferral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    public const COOKIE_NAME = 'onestall_referral_code';
    public const SESSION_CLICK_ID = 'onestall_referral_click_id';
    public const COOKIE_LIFETIME_MINUTES = 60 * 24 * 60; // 60 days

    /**
     * Record a click from a referral link or referral URL param.
     */
    public function recordClick(string $referralCode, ?Request $request = null): ?ReferralClick
    {
        $code = strtoupper(trim($referralCode));
        if (empty($code)) {
            return null;
        }

        // Find referrer by code
        $referrer = User::where('referral_code', $code)->first();
        if (!$referrer) {
            return null;
        }

        $ip = $request ? $request->ip() : request()->ip();
        $userAgent = $request ? $request->userAgent() : request()->userAgent();

        // Self-referral prevention on click: If user is authenticated and matches referrer
        if (auth()->check() && auth()->id() === $referrer->id) {
            return null;
        }

        // Anti-spam throttling: Don't duplicate clicks from same IP on same code within 15 minutes
        $recentClick = ReferralClick::where('referral_code', $code)
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if ($recentClick) {
            // Refresh cookie and session
            session()->put(self::COOKIE_NAME, $code);
            session()->put(self::SESSION_CLICK_ID, $recentClick->id);
            Cookie::queue(self::COOKIE_NAME, $code, self::COOKIE_LIFETIME_MINUTES);
            return $recentClick;
        }

        try {
            $click = ReferralClick::create([
                'referral_code' => $code,
                'referrer_id'   => $referrer->id,
                'ip_address'    => $ip,
                'user_agent'    => $userAgent,
                'referrer_url'  => $request ? $request->header('referer') : request()->header('referer'),
                'landing_page'  => $request ? $request->fullUrl() : request()->fullUrl(),
            ]);

            // Save to session and cookie for attribution during signup
            session()->put(self::COOKIE_NAME, $code);
            session()->put(self::SESSION_CLICK_ID, $click->id);
            Cookie::queue(self::COOKIE_NAME, $code, self::COOKIE_LIFETIME_MINUTES);

            return $click;
        } catch (\Throwable $e) {
            Log::warning('Failed to record referral click: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve the active referral code from input, query, session, or cookie.
     */
    public function resolveActiveCode(?Request $request = null): ?string
    {
        $req = $request ?: request();

        $code = $req->input('referral_code')
            ?: $req->input('ref')
            ?: $req->query('ref')
            ?: session(self::COOKIE_NAME)
            ?: $req->cookie(self::COOKIE_NAME);

        return $code ? strtoupper(trim($code)) : null;
    }

    /**
     * Resolve the active click ID from session or recent record.
     */
    public function resolveClickId(?string $code = null): ?int
    {
        $clickId = session(self::SESSION_CLICK_ID);
        if ($clickId && ReferralClick::where('id', $clickId)->exists()) {
            return (int) $clickId;
        }

        if ($code) {
            $latest = ReferralClick::where('referral_code', $code)
                ->where('ip_address', request()->ip())
                ->latest()
                ->first();
            return $latest?->id;
        }

        return null;
    }

    /**
     * Permanently attribute a newly registered seller to their referrer.
     * Transaction-safe, prevents self-referrals, prevents duplicate referrals.
     */
    public function attributeRegistration(
        User $newUser,
        ?string $referralCode = null,
        ?int $clickId = null,
        ?Request $request = null
    ): ?SellerReferral {
        $code = $referralCode ? strtoupper(trim($referralCode)) : $this->resolveActiveCode($request);
        if (empty($code)) {
            return null;
        }

        return DB::transaction(function () use ($newUser, $code, $clickId, $request) {
            // Lock user row
            $lockedUser = User::lockForUpdate()->find($newUser->id);
            if (!$lockedUser) {
                return null;
            }

            // Duplicate referral check: Has this user already been referred?
            if (!empty($lockedUser->referred_by) || SellerReferral::where('referred_id', $lockedUser->id)->exists()) {
                Log::info("Referral attribution rejected: User ID {$lockedUser->id} is already attributed.");
                return null;
            }

            // Find referrer
            $referrer = User::where('referral_code', $code)->first();
            if (!$referrer) {
                Log::info("Referral attribution rejected: Referral code {$code} does not exist.");
                return null;
            }

            // Self-referral prevention (ID, code, email, phone match)
            if ($referrer->id === $lockedUser->id) {
                Log::warning("Self-referral attempt blocked: User ID {$lockedUser->id} tried to refer themselves.");
                return null;
            }

            if (strtolower(trim($referrer->email)) === strtolower(trim($lockedUser->email))) {
                Log::warning("Self-referral attempt blocked: Email match {$lockedUser->email}.");
                return null;
            }

            if (!empty($referrer->phone) && !empty($lockedUser->phone) && trim($referrer->phone) === trim($lockedUser->phone)) {
                Log::warning("Self-referral attempt blocked: Phone match {$lockedUser->phone}.");
                return null;
            }

            $ip = $request ? $request->ip() : request()->ip();
            $userAgent = $request ? $request->userAgent() : request()->userAgent();
            $resolvedClickId = $clickId ?: $this->resolveClickId($code);
            $deviceFingerprint = $request ? $request->input('device_fingerprint') : request()->input('device_fingerprint');

            // Create permanent attribution record
            $referral = SellerReferral::create([
                'referrer_id'        => $referrer->id,
                'referred_id'        => $lockedUser->id,
                'referral_code'      => $code,
                'click_id'           => $resolvedClickId,
                'status'             => 'registered',
                'ip_address'         => $ip,
                'user_agent'         => $userAgent,
                'device_fingerprint' => $deviceFingerprint,
            ]);

            // Update user record
            $lockedUser->referred_by = $referrer->id;
            $lockedUser->saveQuietly();

            // Run Fraud & Risk Scoring Engine
            app(ReferralFraudDetectionEngine::class)->evaluateReferral($referral, $request);

            // Mark click as converted if exists
            if ($resolvedClickId) {
                ReferralClick::where('id', $resolvedClickId)->update([
                    'converted_at' => now(),
                ]);
            }

            // Clear session & cookie to prevent accidental cross-attribution
            session()->forget([self::COOKIE_NAME, self::SESSION_CLICK_ID]);
            Cookie::queue(Cookie::forget(self::COOKIE_NAME));

            Log::info("Referral attributed successfully: Referrer #{$referrer->id} -> Referred #{$lockedUser->id} (Code: {$code})");

            return $referral;
        });
    }
}
