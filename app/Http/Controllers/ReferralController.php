<?php

namespace App\Http\Controllers;

use App\Models\ReferralCommission;
use App\Models\ReferralPayoutMethod;
use App\Models\ReferralPayoutRequest;
use App\Models\ReferralRule;
use App\Models\SellerReferral;
use App\Services\ReferralPayoutService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    protected ReferralService $referralService;
    protected ReferralPayoutService $payoutService;

    public function __construct(ReferralService $referralService, ReferralPayoutService $payoutService)
    {
        $this->referralService = $referralService;
        $this->payoutService = $payoutService;
    }

    /**
     * Handle direct vanity referral links (e.g., /ref/{code}).
     */
    public function handleRefLink(string $code, Request $request)
    {
        $cleanCode = strtoupper(trim($code));
        $this->referralService->recordClick($cleanCode, $request);

        return redirect()->route('register', ['ref' => $cleanCode]);
    }

    /**
     * Seller Refer & Earn Dashboard.
     */
    public function sellerDashboard(Request $request)
    {
        $user = Auth::user();
        $stats = $user->referral_stats;
        $activeRule = ReferralRule::getActiveRule();

        // Specific metric breakdowns required by user
        $kycApprovedCount = SellerReferral::where('referrer_id', $user->id)
            ->whereHas('referred.kyc', function ($q) {
                $q->where('status', 'approved');
            })
            ->count();

        $eligibleShipmentsCount = ReferralCommission::where('referrer_id', $user->id)
            ->whereNotNull('shipment_id')
            ->count();

        $stats['kyc_approved_referrals'] = $kycApprovedCount;
        $stats['eligible_shipments']     = $eligibleShipmentsCount;
        $stats['min_withdrawal']         = $activeRule ? (float) $activeRule->min_withdrawal_amount : 500.00;

        // 1. Referral History query with search & filter
        $refQuery = SellerReferral::where('referrer_id', $user->id)
            ->with(['referred.kyc'])
            ->withCount('commissions')
            ->orderBy('created_at', 'desc');

        if ($request->filled('ref_search')) {
            $term = $request->query('ref_search');
            $refQuery->whereHas('referred', function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%")
                  ->orWhere('company_name', 'LIKE', "%{$term}%");
            });
        }

        if ($request->filled('ref_status')) {
            $refStatus = $request->query('ref_status');
            if ($refStatus === 'kyc_approved') {
                $refQuery->whereHas('referred.kyc', function ($q) {
                    $q->where('status', 'approved');
                });
            } else {
                $refQuery->where('status', $refStatus);
            }
        }

        $referrals = $refQuery->paginate(10, ['*'], 'referrals_page')->withQueryString();

        // 2. Commission Ledger query with search & status filter
        $commQuery = ReferralCommission::where('referrer_id', $user->id)
            ->with(['referred', 'shipment'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('comm_status')) {
            $commQuery->where('status', $request->query('comm_status'));
        }

        if ($request->filled('comm_search')) {
            $term = $request->query('comm_search');
            $commQuery->where(function ($q) use ($term) {
                $q->whereHas('referred', function ($rq) use ($term) {
                    $rq->where('name', 'LIKE', "%{$term}%")
                       ->orWhere('email', 'LIKE', "%{$term}%");
                })->orWhereHas('shipment', function ($sq) use ($term) {
                    $sq->where('awb_number', 'LIKE', "%{$term}%");
                });
            });
        }

        $commissions = $commQuery->paginate(15, ['*'], 'commissions_page')->withQueryString();

        // 3. Payout Methods
        $payoutMethods = ReferralPayoutMethod::where('user_id', $user->id)->orderBy('is_default', 'desc')->get();

        // 4. Withdrawal / Payout Request History
        $payoutRequests = ReferralPayoutRequest::where('user_id', $user->id)
            ->with('payoutMethod')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'payouts_page');

        return view('seller.referrals.index', compact(
            'user',
            'stats',
            'activeRule',
            'referrals',
            'commissions',
            'payoutMethods',
            'payoutRequests'
        ));
    }

    /**
     * Save/Add Payout Method (Bank Account or UPI).
     */
    public function savePayoutMethod(Request $request)
    {
        $validated = $request->validate([
            'payout_type'         => 'required|in:bank,upi',
            'account_holder_name' => 'required|string|max:150',
            'bank_name'           => 'nullable|required_if:payout_type,bank|string|max:150',
            'account_number'      => 'nullable|required_if:payout_type,bank|string|max:50',
            'ifsc_code'           => 'nullable|required_if:payout_type,bank|string|max:20',
            'upi_id'              => 'nullable|required_if:payout_type,upi|string|max:100',
            'is_default'          => 'nullable|boolean',
        ]);

        $this->payoutService->savePayoutMethod(Auth::user(), $validated);

        return back()->with('success', 'Payout method saved successfully.');
    }

    /**
     * Submit a withdrawal request against available payable referral balance.
     */
    public function requestPayout(Request $request)
    {
        $validated = $request->validate([
            'amount'           => 'required|numeric|min:1',
            'payout_method_id' => 'nullable|exists:referral_payout_methods,id',
        ]);

        $this->payoutService->requestWithdrawal(
            Auth::user(),
            (float) $validated['amount'],
            $validated['payout_method_id'] ?? null
        );

        return back()->with('success', 'Withdrawal request of ₹' . number_format($validated['amount'], 2) . ' submitted successfully. Admin will review and process your payout.');
    }

    /**
     * Instantly transfer referral earnings to shipping recharge wallet.
     */
    public function transferToWallet(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $this->payoutService->transferToShippingWallet(
            Auth::user(),
            (float) $validated['amount']
        );

        return back()->with('success', '₹' . number_format($validated['amount'], 2) . ' successfully transferred from Referral Earnings directly into your Shipping Recharge Wallet!');
    }
}
