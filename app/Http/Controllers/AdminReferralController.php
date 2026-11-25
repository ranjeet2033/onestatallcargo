<?php

namespace App\Http\Controllers;

use App\Models\ReferralAuditLog;
use App\Models\ReferralClick;
use App\Models\ReferralCommission;
use App\Models\ReferralPayoutMethod;
use App\Models\ReferralPayoutRequest;
use App\Models\ReferralRule;
use App\Models\SellerReferral;
use App\Models\User;
use App\Services\ReferralCommissionEngine;
use App\Services\ReferralPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminReferralController extends Controller
{
    protected ReferralCommissionEngine $commissionEngine;
    protected ReferralPayoutService $payoutService;

    public function __construct(
        ReferralCommissionEngine $commissionEngine,
        ReferralPayoutService $payoutService
    ) {
        $this->commissionEngine = $commissionEngine;
        $this->payoutService = $payoutService;
    }

    /**
     * Admin Referral Control Center: Dashboard, Ledger, Payouts, Relationships, Fraud & Rules.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        // Total Clicks vs Total Attributions for Conversion Rate
        $totalClicks = ReferralClick::count();
        $totalAttributions = SellerReferral::count();
        $conversionRate = $totalClicks > 0 ? round(($totalAttributions / $totalClicks) * 100, 1) : 0;

        // Comprehensive Dashboard Analytics
        $stats = [
            'total_commissions' => (float) ReferralCommission::whereIn('status', [
                ReferralCommission::STATUS_APPROVED,
                ReferralCommission::STATUS_PAYABLE,
                ReferralCommission::STATUS_PAID
            ])->sum('commission_amount'),
            'pending_commissions' => (float) ReferralCommission::where('status', ReferralCommission::STATUS_PENDING)->sum('commission_amount'),
            'pending_count'       => ReferralCommission::where('status', ReferralCommission::STATUS_PENDING)->count(),
            'review_count'        => ReferralCommission::where('status', ReferralCommission::STATUS_UNDER_REVIEW)->count(),
            'approved_count'      => ReferralCommission::where('status', ReferralCommission::STATUS_APPROVED)->count(),
            'payable_count'       => ReferralCommission::where('status', ReferralCommission::STATUS_PAYABLE)->count(),
            'paid_count'          => ReferralCommission::where('status', ReferralCommission::STATUS_PAID)->count(),
            'rejected_count'      => ReferralCommission::where('status', ReferralCommission::STATUS_REJECTED)->count(),
            'total_attributions'  => $totalAttributions,
            'total_clicks'        => $totalClicks,
            'conversion_rate'     => $conversionRate,
            'pending_payouts_amt' => (float) ReferralPayoutRequest::where('status', ReferralPayoutRequest::STATUS_PENDING)->sum('amount'),
            'pending_payouts_cnt' => ReferralPayoutRequest::where('status', ReferralPayoutRequest::STATUS_PENDING)->count(),
            'paid_payouts_amt'    => (float) ReferralPayoutRequest::where('status', ReferralPayoutRequest::STATUS_PAID)->sum('amount'),
        ];

        // 1. Commission Ledger Query
        $commQuery = ReferralCommission::with(['referrer', 'referred', 'shipment', 'rule'])
            ->orderBy('created_at', 'desc');

        if (!empty($status)) {
            $commQuery->where('status', $status);
        }

        if (!empty($search)) {
            $commQuery->where(function ($q) use ($search) {
                $q->whereHas('referrer', function ($rq) use ($search) {
                    $rq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('email', 'LIKE', "%{$search}%")
                       ->orWhere('company_name', 'LIKE', "%{$search}%");
                })->orWhereHas('referred', function ($rq) use ($search) {
                    $rq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('email', 'LIKE', "%{$search}%")
                       ->orWhere('company_name', 'LIKE', "%{$search}%");
                })->orWhereHas('shipment', function ($sq) use ($search) {
                    $sq->where('awb_number', 'LIKE', "%{$search}%");
                });
            });
        }

        $commissions = $commQuery->paginate(15, ['*'], 'commissions_page')->withQueryString();

        // 2. Payout Requests Query
        $payoutQuery = ReferralPayoutRequest::with(['user', 'payoutMethod', 'processedByUser'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('payout_status')) {
            $payoutQuery->where('status', $request->query('payout_status'));
        }

        $payoutRequests = $payoutQuery->paginate(15, ['*'], 'payouts_page')->withQueryString();

        // 3. Referral Relationships & Attributions Query
        $attrQuery = SellerReferral::with(['referrer', 'referred.kyc', 'click'])
            ->withCount('commissions')
            ->orderBy('created_at', 'desc');

        if ($request->filled('attr_search')) {
            $attrTerm = $request->query('attr_search');
            $attrQuery->where(function ($q) use ($attrTerm) {
                $q->where('referral_code', 'LIKE', "%{$attrTerm}%")
                  ->orWhereHas('referrer', function ($rq) use ($attrTerm) {
                      $rq->where('name', 'LIKE', "%{$attrTerm}%")->orWhere('email', 'LIKE', "%{$attrTerm}%");
                  })->orWhereHas('referred', function ($rq) use ($attrTerm) {
                      $rq->where('name', 'LIKE', "%{$attrTerm}%")->orWhere('email', 'LIKE', "%{$attrTerm}%");
                  });
            });
        }

        $attributions = $attrQuery->paginate(15, ['*'], 'attributions_page')->withQueryString();

        // 4. Referral Codes Management Query (All sellers with their code & performance)
        $codeQuery = User::whereIn('role', ['seller', 'b2b_customer', 'corporate', 'aggregator'])
            ->withCount(['referralsGiven', 'referralClicks'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('code_search')) {
            $cTerm = $request->query('code_search');
            $codeQuery->where(function ($q) use ($cTerm) {
                $q->where('name', 'LIKE', "%{$cTerm}%")
                  ->orWhere('email', 'LIKE', "%{$cTerm}%")
                  ->orWhere('referral_code', 'LIKE', "%{$cTerm}%");
            });
        }

        $sellerCodes = $codeQuery->paginate(15, ['*'], 'codes_page')->withQueryString();

        // 5. Rules & Campaign Settings
        $rules = ReferralRule::orderBy('id', 'desc')->get();
        $activeRule = ReferralRule::getActiveRule();

        // 6. Fraud Investigation Signals
        $fraudSignals = $this->detectFraudSignals();

        // 7. Audit Logs
        $auditLogs = ReferralAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        return view('admin.referrals.index', compact(
            'stats',
            'commissions',
            'payoutRequests',
            'attributions',
            'sellerCodes',
            'rules',
            'activeRule',
            'fraudSignals',
            'auditLogs'
        ));
    }

    /**
     * Commission Status Transition Workflow (Approve, Payable, Paid, Reject).
     */
    public function updateCommissionStatus(Request $request, $id)
    {
        $commission = ReferralCommission::findOrFail($id);
        $action = $request->input('action');
        $adminId = Auth::id();

        switch ($action) {
            case 'approve':
                $this->commissionEngine->approveCommission($commission, $adminId);
                ReferralAuditLog::log('commission_approved', $commission, ['amount' => $commission->commission_amount], $adminId);
                $msg = "Commission #{$commission->id} has been approved.";
                break;

            case 'mark_payable':
                $this->commissionEngine->markPayable($commission);
                ReferralAuditLog::log('commission_payable', $commission, ['amount' => $commission->commission_amount], $adminId);
                $msg = "Commission #{$commission->id} moved to Payable.";
                break;

            case 'mark_paid':
                $request->validate([
                    'payment_reference' => 'required|string|max:100',
                    'payout_notes'      => 'nullable|string',
                ]);
                $this->commissionEngine->markPaid(
                    $commission,
                    $request->input('payment_reference'),
                    $adminId,
                    $request->input('payout_notes')
                );
                ReferralAuditLog::log('commission_paid', $commission, ['reference' => $request->payment_reference], $adminId);
                $msg = "Commission #{$commission->id} marked as Paid with reference '{$request->payment_reference}'.";
                break;

            case 'reject':
                $request->validate([
                    'rejection_reason' => 'required|string|max:500',
                ]);
                $this->commissionEngine->rejectCommission(
                    $commission,
                    $request->input('rejection_reason'),
                    $adminId
                );
                ReferralAuditLog::log('commission_rejected', $commission, ['reason' => $request->rejection_reason], $adminId);
                $msg = "Commission #{$commission->id} rejected.";
                break;

            default:
                return back()->with('error', 'Invalid commission action requested.');
        }

        return back()->with('success', $msg);
    }

    /**
     * Bulk Approve Pending Commissions.
     */
    public function bulkApproveCommissions(Request $request)
    {
        $validated = $request->validate([
            'commission_ids'   => 'required|array',
            'commission_ids.*' => 'exists:referral_commissions,id',
        ]);

        $adminId = Auth::id();
        $count = 0;

        foreach ($validated['commission_ids'] as $cid) {
            $comm = ReferralCommission::find($cid);
            if ($comm && in_array($comm->status, [ReferralCommission::STATUS_PENDING, ReferralCommission::STATUS_UNDER_REVIEW])) {
                $this->commissionEngine->approveCommission($comm, $adminId);
                $count++;
            }
        }

        ReferralAuditLog::log('commissions_bulk_approved', null, ['count' => $count], $adminId);

        return back()->with('success', "{$count} pending commissions approved successfully.");
    }

    /**
     * Process Payout Request (Approve, Mark Processing, Mark Paid, Reject).
     */
    public function processPayout(Request $request, $id)
    {
        $payoutRequest = ReferralPayoutRequest::findOrFail($id);
        $action = $request->input('action');
        $adminId = Auth::id();

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payout_proofs', 'public');
        }

        $this->payoutService->processPayout($payoutRequest, $action, $adminId, [
            'payment_reference'  => $request->input('payment_reference'),
            'rejection_reason'   => $request->input('rejection_reason'),
            'admin_notes'        => $request->input('admin_notes'),
            'payment_proof_path' => $proofPath,
        ]);

        return back()->with('success', "Payout request #{$payoutRequest->id} updated successfully.");
    }

    /**
     * Update an existing referral rule or campaign configuration.
     */
    public function updateRule(Request $request, $id)
    {
        $rule = ReferralRule::findOrFail($id);

        $validated = $request->validate([
            'name'                        => 'required|string|max:100',
            'reward_type'                 => 'required|in:fixed,percentage',
            'reward_value'                => 'required|numeric|min:0',
            'trigger_event'               => 'required|in:first_delivered_shipment,every_delivered_shipment',
            'min_order_value'             => 'required|numeric|min:0',
            'validity_days'               => 'required|integer|min:1',
            'max_commission_per_referral' => 'nullable|numeric|min:0',
            'min_withdrawal_amount'       => 'required|numeric|min:1',
            'requires_kyc'                => 'nullable|boolean',
            'requires_phone_verified'     => 'nullable|boolean',
            'auto_approve_commission'     => 'nullable|boolean',
            'is_active'                   => 'nullable|boolean',
        ]);

        $validated['requires_kyc'] = $request->has('requires_kyc');
        $validated['requires_phone_verified'] = $request->has('requires_phone_verified');
        $validated['auto_approve_commission'] = $request->has('auto_approve_commission');
        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            ReferralRule::where('id', '!=', $rule->id)->update(['is_active' => false]);
        }

        $rule->update($validated);

        ReferralAuditLog::log('rule_updated', $rule, $validated, Auth::id());

        return back()->with('success', "Referral rule '{$rule->name}' updated successfully.");
    }

    /**
     * Create a new referral rule.
     */
    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'name'                        => 'required|string|max:100',
            'reward_type'                 => 'required|in:fixed,percentage',
            'reward_value'                => 'required|numeric|min:0',
            'trigger_event'               => 'required|in:first_delivered_shipment,every_delivered_shipment',
            'min_order_value'             => 'required|numeric|min:0',
            'validity_days'               => 'required|integer|min:1',
            'max_commission_per_referral' => 'nullable|numeric|min:0',
            'min_withdrawal_amount'       => 'required|numeric|min:1',
            'requires_kyc'                => 'nullable|boolean',
            'requires_phone_verified'     => 'nullable|boolean',
            'auto_approve_commission'     => 'nullable|boolean',
            'is_active'                   => 'nullable|boolean',
        ]);

        $validated['requires_kyc'] = $request->has('requires_kyc');
        $validated['requires_phone_verified'] = $request->has('requires_phone_verified');
        $validated['auto_approve_commission'] = $request->has('auto_approve_commission');
        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_active']) {
            ReferralRule::query()->update(['is_active' => false]);
        }

        $rule = ReferralRule::create($validated);

        ReferralAuditLog::log('rule_created', $rule, $validated, Auth::id());

        return back()->with('success', "New referral rule '{$rule->name}' created successfully.");
    }

    /**
     * Regenerate or assign custom referral code for a seller.
     */
    public function regenerateReferralCode(Request $request, $userId)
    {
        $seller = User::findOrFail($userId);

        if ($request->filled('custom_code')) {
            $code = strtoupper(trim($request->input('custom_code')));
            $exists = User::where('referral_code', $code)->where('id', '!=', $seller->id)->exists();
            if ($exists) {
                return back()->with('error', "Referral code '{$code}' is already taken by another user.");
            }
            $seller->referral_code = $code;
        } else {
            $seller->referral_code = null;
            $seller->getOrGenerateReferralCode();
        }

        $seller->save();

        ReferralAuditLog::log('referral_code_updated', $seller, ['new_code' => $seller->referral_code], Auth::id());

        return back()->with('success', "Referral code for {$seller->name} updated to {$seller->referral_code}.");
    }

    /**
     * Export Referral Commissions Ledger to CSV.
     */
    public function exportCommissionsCsv(Request $request)
    {
        $fileName = 'referral_commissions_ledger_' . date('Y_m_d_His') . '.csv';
        $commissions = ReferralCommission::with(['referrer', 'referred', 'shipment'])->orderBy('id', 'desc')->get();

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Commission ID', 'Date', 'Beneficiary (Referrer)', 'Referrer Email',
            'Referred Shipper', 'Referred Email', 'AWB Number', 'Freight Base (INR)',
            'Commission Amount (INR)', 'Type', 'Rate Applied', 'Status',
            'Approved At', 'Paid At', 'Payment Reference', 'Rejection Reason'
        ];

        $callback = function () use ($commissions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($commissions as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->created_at->format('Y-m-d H:i:s'),
                    $c->referrer ? ($c->referrer->company_name ?: $c->referrer->name) : 'N/A',
                    $c->referrer ? $c->referrer->email : 'N/A',
                    $c->referred ? ($c->referred->company_name ?: $c->referred->name) : 'N/A',
                    $c->referred ? $c->referred->email : 'N/A',
                    $c->shipment ? $c->shipment->awb_number : 'N/A',
                    $c->base_amount,
                    $c->commission_amount,
                    $c->commission_type,
                    $c->rate_applied,
                    $c->status,
                    $c->approved_at ? $c->approved_at->format('Y-m-d H:i:s') : '',
                    $c->paid_at ? $c->paid_at->format('Y-m-d H:i:s') : '',
                    $c->payment_reference ?? '',
                    $c->rejection_reason ?? '',
                ]);
            }

            fclose($file);
        };

        ReferralAuditLog::log('commissions_csv_exported', null, ['count' => $commissions->count()], Auth::id());

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Payout Requests Ledger to CSV.
     */
    public function exportPayoutsCsv(Request $request)
    {
        $fileName = 'referral_payout_requests_' . date('Y_m_d_His') . '.csv';
        $payouts = ReferralPayoutRequest::with(['user', 'payoutMethod'])->orderBy('id', 'desc')->get();

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'Payout ID', 'Request Date', 'Seller Name', 'Seller Email',
            'Amount (INR)', 'Payout Destination', 'Status', 'Payment Reference (UTR)',
            'Processed At', 'Paid At', 'Notes / Rejection Reason'
        ];

        $callback = function () use ($payouts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payouts as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->created_at->format('Y-m-d H:i:s'),
                    $p->user ? ($p->user->company_name ?: $p->user->name) : 'N/A',
                    $p->user ? $p->user->email : 'N/A',
                    $p->amount,
                    $p->method_display,
                    $p->status,
                    $p->payment_reference ?? '',
                    $p->processed_at ? $p->processed_at->format('Y-m-d H:i:s') : '',
                    $p->paid_at ? $p->paid_at->format('Y-m-d H:i:s') : '',
                    $p->rejection_reason ?: $p->admin_notes ?: '',
                ]);
            }

            fclose($file);
        };

        ReferralAuditLog::log('payouts_csv_exported', null, ['count' => $payouts->count()], Auth::id());

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Admin Investigation Action: Hold, Release, Reject, or Verify flagged referrals/commissions.
     */
    public function investigateTarget(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|in:referral,commission,payout',
            'target_id'   => 'required|integer',
            'action'      => 'required|in:hold,release,reject,verify',
            'findings'    => 'required|string|min:5|max:1000',
        ]);

        $admin = Auth::user();
        if (!$admin->isAdmin()) {
            return back()->with('error', 'Unauthorized action. Admin privileges required.');
        }

        $referral = null;
        $commission = null;
        $payoutRequest = null;
        $targetUser = null;

        if ($validated['target_type'] === 'referral') {
            $referral = SellerReferral::findOrFail($validated['target_id']);
            $targetUser = $referral->referred ?: $referral->referrer;
        } elseif ($validated['target_type'] === 'commission') {
            $commission = ReferralCommission::findOrFail($validated['target_id']);
            $targetUser = $commission->referrer;
            $referral = $commission->referral;
        } elseif ($validated['target_type'] === 'payout') {
            $payoutRequest = ReferralPayoutRequest::findOrFail($validated['target_id']);
            $targetUser = $payoutRequest->user;
        }

        if (!$targetUser) {
            return back()->with('error', 'Target user account not found.');
        }

        $investigationEngine = app(\App\Services\ReferralFraudDetectionEngine::class);
        $investigationEngine->recordInvestigation(
            $admin,
            $targetUser,
            $validated['action'],
            $validated['findings'],
            $referral,
            $commission,
            $payoutRequest
        );

        return back()->with('success', "Investigation action '{$validated['action']}' applied and logged successfully.");
    }

    /**
     * Detect heuristic fraud patterns and flagged accounts across referrals.
     */
    protected function detectFraudSignals(): array
    {
        // 1. Flagged & High-Risk Referrals
        $flaggedReferrals = SellerReferral::with(['referrer', 'referred.kyc'])
            ->where(function ($q) {
                $q->where('is_flagged', true)
                  ->orWhere('is_held', true)
                  ->orWhere('risk_score', '>=', 30);
            })
            ->orderBy('risk_score', 'desc')
            ->take(25)
            ->get();

        // 2. Flagged & Held Commissions
        $flaggedCommissions = ReferralCommission::with(['referrer', 'referred', 'shipment'])
            ->where(function ($q) {
                $q->where('is_held', true)
                  ->orWhere('risk_score', '>=', 30)
                  ->orWhere('status', ReferralCommission::STATUS_UNDER_REVIEW);
            })
            ->orderBy('risk_score', 'desc')
            ->take(25)
            ->get();

        // 3. Flagged Payout Requests
        $flaggedPayouts = ReferralPayoutRequest::with(['user', 'payoutMethod'])
            ->where('risk_score', '>=', 30)
            ->orderBy('risk_score', 'desc')
            ->take(25)
            ->get();

        // 4. IP Clusters: Multiple referrals sharing the same IP address
        $ipClusters = SellerReferral::select('ip_address', DB::raw('count(*) as total'))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->having('total', '>', 2)
            ->take(10)
            ->get();

        // 5. Duplicate Bank Accounts linked across multiple seller user accounts
        $duplicateBankMethods = ReferralPayoutMethod::select('account_number', DB::raw('count(DISTINCT user_id) as user_count'))
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->groupBy('account_number')
            ->having('user_count', '>', 1)
            ->take(10)
            ->get();

        // 6. Duplicate UPI IDs linked across multiple seller user accounts
        $duplicateUpiMethods = ReferralPayoutMethod::select('upi_id', DB::raw('count(DISTINCT user_id) as user_count'))
            ->whereNotNull('upi_id')
            ->where('upi_id', '!=', '')
            ->groupBy('upi_id')
            ->having('user_count', '>', 1)
            ->take(10)
            ->get();

        // 7. Recent Investigation History Log
        $investigationHistory = \App\Models\ReferralFraudInvestigation::with(['user', 'admin', 'referral'])
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        return [
            'flagged_referrals'     => $flaggedReferrals,
            'flagged_commissions'   => $flaggedCommissions,
            'flagged_payouts'       => $flaggedPayouts,
            'ip_clusters'           => $ipClusters,
            'duplicate_bank_accounts'=> $duplicateBankMethods,
            'duplicate_upis'        => $duplicateUpiMethods,
            'investigation_history' => $investigationHistory,
        ];
    }
}
