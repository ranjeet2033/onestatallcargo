@extends('layouts.admin')

@section('title', 'Referral & Earn Management - OneStall Cargo')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ request('tab', 'ledger') }}',
    showPayModal: false,
    payData: {},
    showRejectModal: false,
    rejectData: {},
    showPayoutActionModal: false,
    payoutActionData: {},
    showCodeModal: false,
    codeData: {},
    showRuleModal: false,
    showInvestigateModal: false,
    investigateData: {},
    selectedCommissions: []
}">

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Seller Referral & Earn Engine</h1>
            <p class="text-xs text-gray-500 mt-1">Multi-tier commission configuration, fraud signals, ledger approvals, and withdrawal disbursements.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- CSV Export Buttons -->
            <a href="{{ route('admin.referrals.export.commissions') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-csv text-emerald-600"></i> Export Commissions
            </a>
            <a href="{{ route('admin.referrals.export.payouts') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-csv text-blue-600"></i> Export Payouts
            </a>
            <button @click="showRuleModal = true" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-900 hover:bg-black text-white shadow transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> New Campaign Rule
            </button>
        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-xs flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-50 text-red-800 border border-red-200 font-bold text-xs flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 text-red-800 border border-red-200 text-xs shadow-sm space-y-1">
            <div class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-xmark"></i> Validation errors:</div>
            <ul class="list-disc pl-5 font-medium">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Comprehensive Dashboard Analytics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- 1. Total Attributions -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Attributions</div>
            <div class="text-xl font-black text-gray-900">{{ number_format($stats['total_attributions']) }}</div>
            <div class="text-[10px] text-gray-500">{{ $stats['conversion_rate'] }}% click conversion</div>
        </div>

        <!-- 2. Settled/Paid Earnings -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Total Paid Out</div>
            <div class="text-xl font-black text-emerald-600">₹{{ number_format($stats['paid_payouts_amt'], 2) }}</div>
            <div class="text-[10px] text-gray-400">Direct disbursements</div>
        </div>

        <!-- 3. Pending Commissions -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-amber-500">Pending Review</div>
            <div class="text-xl font-black text-amber-600">{{ number_format($stats['pending_count']) }}</div>
            <div class="text-[10px] text-amber-500 font-semibold">₹{{ number_format($stats['pending_commissions'], 2) }} volume</div>
        </div>

        <!-- 4. Under Review -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-blue-500">Under Review</div>
            <div class="text-xl font-black text-blue-600">{{ number_format($stats['review_count']) }}</div>
            <div class="text-[10px] text-gray-400">Awaiting KYC approval</div>
        </div>

        <!-- 5. Payable / Approved -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-500">Approved / Payable</div>
            <div class="text-xl font-black text-indigo-600">{{ number_format($stats['approved_count'] + $stats['payable_count']) }}</div>
            <div class="text-[10px] text-gray-400">Available to withdraw</div>
        </div>

        <!-- 6. Pending Withdrawal Requests -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-2xl border border-amber-200 shadow-sm space-y-1">
            <div class="text-[10px] font-extrabold uppercase tracking-wider text-amber-800">Pending Payouts</div>
            <div class="text-xl font-black text-amber-700">{{ number_format($stats['pending_payouts_cnt']) }}</div>
            <div class="text-[10px] text-amber-600 font-bold">₹{{ number_format($stats['pending_payouts_amt'], 2) }} queued</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 overflow-x-auto">
        <button type="button" @click="activeTab = 'ledger'" :class="activeTab === 'ledger' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-receipt"></i> Commission Ledger ({{ $commissions->total() }})
        </button>

        <button type="button" @click="activeTab = 'payouts'" :class="activeTab === 'payouts' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-money-bill-transfer"></i> Payout Requests ({{ $payoutRequests->total() }})
            @if($stats['pending_payouts_cnt'] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-amber-500 text-white">{{ $stats['pending_payouts_cnt'] }}</span>
            @endif
        </button>

        <button type="button" @click="activeTab = 'attributions'" :class="activeTab === 'attributions' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-link"></i> Relationships ({{ $attributions->total() }})
        </button>

        <button type="button" @click="activeTab = 'codes'" :class="activeTab === 'codes' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-qrcode"></i> Codes & Vanity ({{ $sellerCodes->total() }})
        </button>

        <button type="button" @click="activeTab = 'rules'" :class="activeTab === 'rules' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-sliders"></i> Rules & Campaigns ({{ $rules->count() }})
        </button>

        <button type="button" @click="activeTab = 'fraud'" :class="activeTab === 'fraud' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-shield-halved text-red-500"></i> Fraud Signals
        </button>

        <button type="button" @click="activeTab = 'audit'" :class="activeTab === 'audit' ? 'border-gray-900 text-gray-900 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 px-2 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-clock-rotate-left"></i> Audit Logs
        </button>
    </div>

    <!-- TAB 1: COMMISSION LEDGER -->
    <div x-show="activeTab === 'ledger'" class="space-y-4">
        <!-- Filter & Bulk Actions Bar -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <form action="{{ route('admin.referrals.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="ledger">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Referrer, Referred, AWB..." class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs w-64 focus:outline-none focus:border-gray-900">
                
                <select name="status" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-gray-900">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="payable" {{ request('status') === 'payable' ? 'selected' : '' }}>Payable</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">
                    Filter
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.referrals.index', ['tab' => 'ledger']) }}" class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-900">Reset</a>
                @endif
            </form>

            <!-- Bulk Approval Form -->
            <form action="{{ route('admin.referrals.commissions.bulk-approve') }}" method="POST" class="flex items-center gap-2" x-show="selectedCommissions.length > 0" x-cloak>
                @csrf
                <template x-for="id in selectedCommissions" :key="id">
                    <input type="hidden" name="commission_ids[]" :value="id">
                </template>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow flex items-center gap-1.5">
                    <i class="fa-solid fa-check-double"></i> Approve Selected (<span x-text="selectedCommissions.length"></span>)
                </button>
            </form>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-4 w-8">
                                <input type="checkbox" @change="$el.checked ? selectedCommissions = [{{ $commissions->pluck('id')->join(',') }}] : selectedCommissions = []" class="rounded text-indigo-600">
                            </th>
                            <th class="px-4 py-4">ID / Date</th>
                            <th class="px-4 py-4">Beneficiary (Referrer)</th>
                            <th class="px-4 py-4">Referred Shipper</th>
                            <th class="px-4 py-4">Shipment (AWB)</th>
                            <th class="px-4 py-4">Freight Base</th>
                            <th class="px-4 py-4">Commission</th>
                            <th class="px-4 py-4">Status & Notes</th>
                            <th class="px-4 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($commissions as $comm)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-4 py-4">
                                    <input type="checkbox" :value="{{ $comm->id }}" x-model="selectedCommissions" class="rounded text-indigo-600">
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-900">#COMM-{{ $comm->id }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $comm->created_at->format('d M Y, h:i A') }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-900">{{ $comm->referrer->company_name ?: $comm->referrer->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $comm->referrer->email }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-900">{{ $comm->referred->company_name ?: $comm->referred->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $comm->referred->email }}</div>
                                </td>
                                <td class="px-4 py-4 font-mono font-bold text-indigo-600">
                                    {{ $comm->shipment ? $comm->shipment->awb_number : 'N/A' }}
                                </td>
                                <td class="px-4 py-4">
                                    ₹{{ number_format($comm->base_amount, 2) }}
                                </td>
                                <td class="px-4 py-4 font-extrabold text-emerald-600 text-sm">
                                    ₹{{ number_format($comm->commission_amount, 2) }}
                                    <span class="text-[10px] font-normal text-gray-400">({{ $comm->commission_type === 'percentage' ? $comm->rate_applied.'%' : 'flat' }})</span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1.5">
                                        @switch($comm->status)
                                            @case('approved')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Approved</span>
                                                @break
                                            @case('payable')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Payable</span>
                                                @break
                                            @case('paid')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                                                    Paid ({{ $comm->payment_reference ?: 'Settled' }})
                                                </span>
                                                @break
                                            @case('under_review')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100" title="{{ $comm->rejection_reason }}">Under Review</span>
                                                @break
                                            @case('rejected')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100" title="{{ $comm->rejection_reason }}">Rejected</span>
                                                @break
                                            @default
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">Pending</span>
                                        @endswitch

                                        @if($comm->rejection_reason)
                                            <span class="text-[10px] text-gray-400 italic truncate max-w-xs" title="{{ $comm->rejection_reason }}">
                                                {{ $comm->rejection_reason }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right space-x-1">
                                    @if($comm->status === 'pending' || $comm->status === 'under_review')
                                        <form action="{{ route('admin.referrals.commission.status', $comm->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] transition">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if($comm->status === 'approved')
                                        <form action="{{ route('admin.referrals.commission.status', $comm->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="mark_payable">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] transition">
                                                To Payable
                                            </button>
                                        </form>
                                    @endif

                                    @if($comm->status === 'payable' || $comm->status === 'approved')
                                        <button type="button" @click="payData = { id: {{ $comm->id }}, amount: '{{ $comm->commission_amount }}', beneficiary: '{{ addslashes($comm->referrer->name) }}' }; showPayModal = true" class="px-2.5 py-1 rounded-lg bg-gray-900 hover:bg-black text-white font-bold text-[10px] transition">
                                            Mark Paid
                                        </button>
                                    @endif

                                    @if($comm->status !== 'paid' && $comm->status !== 'rejected')
                                        <button type="button" @click="rejectData = { id: {{ $comm->id }} }; showRejectModal = true" class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[10px] transition">
                                            Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    No commission records matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($commissions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $commissions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: PAYOUT REQUESTS & DISBURSEMENTS -->
    <div x-show="activeTab === 'payouts'" x-cloak class="space-y-4">
        <!-- Payout Filter -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <form action="{{ route('admin.referrals.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="payouts">
                <select name="payout_status" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-gray-900">
                    <option value="">All Payout Statuses</option>
                    <option value="pending" {{ request('payout_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('payout_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="approved" {{ request('payout_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('payout_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('payout_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Payout Requests Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Request ID / Date</th>
                            <th class="px-6 py-4">Seller Details</th>
                            <th class="px-6 py-4">Withdrawal Amount</th>
                            <th class="px-6 py-4">Payout Destination</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Bank Reference / UTR</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($payoutRequests as $pr)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">#PAYOUT-{{ $pr->id }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $pr->created_at->format('d M Y, h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $pr->user ? ($pr->user->company_name ?: $pr->user->name) : 'N/A' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $pr->user ? $pr->user->email : '' }}</div>
                                </td>
                                <td class="px-6 py-4 font-black text-gray-900 text-sm">
                                    ₹{{ number_format($pr->amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $pr->method_display }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @switch($pr->status)
                                        @case('paid')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <i class="fa-solid fa-circle-check"></i> Paid
                                            </span>
                                            @break
                                        @case('processing')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                <i class="fa-solid fa-spinner fa-spin text-[9px]"></i> Processing
                                            </span>
                                            @break
                                        @case('approved')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                Approved
                                            </span>
                                            @break
                                        @case('rejected')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100" title="{{ $pr->rejection_reason }}">
                                                Rejected
                                            </span>
                                            @break
                                        @default
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                                Pending Review
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-gray-900">
                                    @if($pr->payment_reference)
                                        <span class="text-emerald-700">{{ $pr->payment_reference }}</span>
                                    @else
                                        <span class="text-gray-400 font-normal italic">Pending UTR</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-1">
                                    @if($pr->status === 'pending')
                                        <form action="{{ route('admin.referrals.payouts.process', $pr->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="action" value="mark_processing">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] transition">
                                                Process
                                            </button>
                                        </form>
                                    @endif

                                    @if($pr->status !== 'paid' && $pr->status !== 'rejected')
                                        <button type="button" @click="payoutActionData = { id: {{ $pr->id }}, amount: '{{ $pr->amount }}', seller: '{{ addslashes($pr->user ? $pr->user->name : '') }}', destination: '{{ addslashes($pr->method_display) }}' }; showPayoutActionModal = true" class="px-3 py-1 rounded-lg bg-gray-900 hover:bg-black text-white font-bold text-[10px] transition">
                                            Disburse / Pay
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                    No payout requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payoutRequests->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $payoutRequests->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: RELATIONSHIPS & ATTRIBUTIONS -->
    <div x-show="activeTab === 'attributions'" x-cloak class="space-y-4">
        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between gap-3">
            <form action="{{ route('admin.referrals.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="attributions">
                <input type="text" name="attr_search" value="{{ request('attr_search') }}" placeholder="Search Code, Referrer, Referred..." class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs w-72 focus:outline-none focus:border-gray-900">
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Search</button>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Attributed At</th>
                            <th class="px-6 py-4">Referrer (Inviter)</th>
                            <th class="px-6 py-4">Referred (New Seller)</th>
                            <th class="px-6 py-4">Referral Code</th>
                            <th class="px-6 py-4">Referred KYC Status</th>
                            <th class="px-6 py-4">Referral Status</th>
                            <th class="px-6 py-4">Rewarded Orders</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($attributions as $attr)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $attr->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $attr->referrer ? ($attr->referrer->company_name ?: $attr->referrer->name) : 'N/A' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $attr->referrer ? $attr->referrer->email : '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $attr->referred ? ($attr->referred->company_name ?: $attr->referred->name) : 'N/A' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $attr->referred ? $attr->referred->email : '' }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-indigo-600 uppercase">
                                    {{ $attr->referral_code }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($attr->referred && $attr->referred->isKycApproved())
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">KYC Approved</span>
                                    @elseif($attr->referred && $attr->referred->kyc)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">KYC Pending</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-50 text-gray-500">Not Submitted</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-700">{{ $attr->status }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ $attr->commissions_count }} shipment(s)
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                    No attribution relationships found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attributions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $attributions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 4: REFERRAL CODE MANAGEMENT -->
    <div x-show="activeTab === 'codes'" x-cloak class="space-y-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between gap-3">
            <form action="{{ route('admin.referrals.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="codes">
                <input type="text" name="code_search" value="{{ request('code_search') }}" placeholder="Search Seller or Referral Code..." class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs w-72 focus:outline-none focus:border-gray-900">
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Seller Name & Company</th>
                            <th class="px-6 py-4">Assigned Referral Code</th>
                            <th class="px-6 py-4">Link Clicks</th>
                            <th class="px-6 py-4">Total Referred Sellers</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($sellerCodes as $sc)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $sc->company_name ?: $sc->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $sc->email }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-indigo-700 uppercase">
                                    {{ $sc->referral_code ?: $sc->getOrGenerateReferralCode() }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-bold">
                                    {{ number_format($sc->referral_clicks_count) }} clicks
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ number_format($sc->referrals_given_count) }} signups
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="codeData = { id: {{ $sc->id }}, name: '{{ addslashes($sc->name) }}', code: '{{ $sc->referral_code }}' }; showCodeModal = true" class="px-3 py-1 rounded-lg border border-gray-200 hover:border-gray-900 font-bold text-[10px] text-gray-700 transition">
                                        <i class="fa-solid fa-pen-to-square"></i> Custom Code
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                    No sellers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sellerCodes->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $sellerCodes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 5: RULES & CAMPAIGNS -->
    <div x-show="activeTab === 'rules'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($rules as $r)
                <div class="bg-white p-6 rounded-3xl border {{ $r->is_active ? 'border-emerald-300 ring-2 ring-emerald-400/20 shadow-md' : 'border-gray-200' }} space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-extrabold text-base text-gray-900">{{ $r->name }}</h3>
                            <span class="text-[11px] text-gray-400">ID #{{ $r->id }} &bull; Created {{ $r->created_at->format('d M Y') }}</span>
                        </div>
                        @if($r->is_active)
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">Active Campaign</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </div>

                    <form action="{{ route('admin.referrals.rules.update', $r->id) }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Reward Type</label>
                                <select name="reward_type" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                                    <option value="percentage" {{ $r->reward_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ $r->reward_type === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Referrer Reward</label>
                                <input type="number" step="0.01" name="reward_value" value="{{ $r->reward_value }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Referee Bonus (₹)</label>
                                <input type="number" step="0.01" name="referee_reward_value" value="{{ $r->referee_reward_value ?? $r->reward_value }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Trigger Event</label>
                                <select name="trigger_event" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                                    <option value="first_wallet_recharge" {{ $r->trigger_event === 'first_wallet_recharge' ? 'selected' : '' }}>1st Wallet Recharge (Both Sellers)</option>
                                    <option value="first_delivered_shipment" {{ $r->trigger_event === 'first_delivered_shipment' ? 'selected' : '' }}>1st Delivered Order</option>
                                    <option value="every_delivered_shipment" {{ $r->trigger_event === 'every_delivered_shipment' ? 'selected' : '' }}>Every Delivered Order</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Min Freight Order Value (₹)</label>
                                <input type="number" step="0.01" name="min_order_value" value="{{ $r->min_order_value }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Eligibility (Days)</label>
                                <input type="number" name="validity_days" value="{{ $r->validity_days }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Max Cap (₹)</label>
                                <input type="number" step="0.01" name="max_commission_per_referral" value="{{ $r->max_commission_per_referral }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" placeholder="Limit">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Min Payout (₹)</label>
                                <input type="number" step="0.01" name="min_withdrawal_amount" value="{{ $r->min_withdrawal_amount ?? 500 }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                            <label class="flex items-center gap-1.5 text-gray-700 cursor-pointer">
                                <input type="checkbox" name="requires_kyc" value="1" {{ $r->requires_kyc ? 'checked' : '' }} class="rounded text-indigo-600">
                                <span>Require Approved KYC</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $r->is_active ? 'checked' : '' }} class="rounded text-indigo-600">
                                <span>Active Campaign</span>
                            </label>
                        </div>

                        <input type="hidden" name="name" value="{{ $r->name }}">

                        <div class="pt-2 text-right">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs transition">
                                Save Rule
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 6: FRAUD INVESTIGATION & RISK SCORING -->
    <div x-show="activeTab === 'fraud'" x-cloak class="space-y-6">
        <!-- Heuristic Risk Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-red-50/80 border border-red-200 p-4 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-red-600 block">Critical Risk</span>
                    <span class="text-xl font-black text-red-900">{{ $fraudSignals['flagged_referrals']->where('risk_level', 'critical')->count() }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-200/60 flex items-center justify-center text-red-700">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="bg-amber-50/80 border border-amber-200 p-4 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 block">High Risk</span>
                    <span class="text-xl font-black text-amber-900">{{ $fraudSignals['flagged_referrals']->where('risk_level', 'high')->count() }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-200/60 flex items-center justify-center text-amber-700">
                    <i class="fa-solid fa-shield-virus"></i>
                </div>
            </div>
            <div class="bg-blue-50/80 border border-blue-200 p-4 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 block">Medium Risk</span>
                    <span class="text-xl font-black text-blue-900">{{ $fraudSignals['flagged_referrals']->where('risk_level', 'medium')->count() }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-200/60 flex items-center justify-center text-blue-700">
                    <i class="fa-solid fa-eye"></i>
                </div>
            </div>
            <div class="bg-purple-50/80 border border-purple-200 p-4 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 block">Held Under Review</span>
                    <span class="text-xl font-black text-purple-900">{{ $fraudSignals['flagged_referrals']->where('is_held', true)->count() }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-200/60 flex items-center justify-center text-purple-700">
                    <i class="fa-solid fa-hand"></i>
                </div>
            </div>
        </div>

        <!-- 1. Flagged & High Risk Referrals Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-red-600"></i> Flagged Seller Referrals
                    </h3>
                    <p class="text-xs text-gray-400">Scored by multi-factor engine (IP, device fingerprint, phone, email alias, KYC match, bank collision)</p>
                </div>
                <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    Manual Review Mandatory — Zero Auto-Rejection
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Referral ID</th>
                            <th class="px-6 py-4">Referrer Account</th>
                            <th class="px-6 py-4">Referred Seller</th>
                            <th class="px-6 py-4">Risk Score & Classification</th>
                            <th class="px-6 py-4">Triggered Signals</th>
                            <th class="px-6 py-4">Attribution Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($fraudSignals['flagged_referrals'] as $fr)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-gray-700">#{{ $fr->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $fr->referrer ? ($fr->referrer->company_name ?: $fr->referrer->name) : 'N/A' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $fr->referrer?->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $fr->referred ? ($fr->referred->company_name ?: $fr->referred->name) : 'N/A' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $fr->referred?->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            @if($fr->risk_level === 'critical') bg-red-100 text-red-800 border border-red-200
                                            @elseif($fr->risk_level === 'high') bg-amber-100 text-amber-800 border border-amber-200
                                            @elseif($fr->risk_level === 'medium') bg-blue-100 text-blue-800 border border-blue-200
                                            @else bg-emerald-100 text-emerald-800 border border-emerald-200 @endif">
                                            {{ strtoupper($fr->risk_level) }} ({{ $fr->risk_score }}/100)
                                        </span>
                                        @if($fr->is_held)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-800">HELD</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-xs space-y-1">
                                        @if(!empty($fr->risk_factors))
                                            @foreach(array_slice($fr->risk_factors, 0, 2) as $factor)
                                                <div class="text-[11px] text-gray-600 truncate flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-dot text-[8px] text-red-500"></i>
                                                    {{ $factor['description'] ?? $factor['code'] }}
                                                </div>
                                            @endforeach
                                            @if(count($fr->risk_factors) > 2)
                                                <span class="text-[10px] text-gray-400 font-bold">+{{ count($fr->risk_factors) - 2 }} more signals</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic">No factors recorded</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                                        {{ $fr->status === 'disqualified' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($fr->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="investigateData = {
                                        id: {{ $fr->id }},
                                        type: 'referral',
                                        seller: '{{ addslashes($fr->referred?->name ?? 'Seller') }}',
                                        score: {{ $fr->risk_score }},
                                        level: '{{ $fr->risk_level }}',
                                        factors: {{ json_encode($fr->risk_factors ?? []) }}
                                    }; showInvestigateModal = true"
                                    class="px-3 py-1.5 rounded-xl bg-gray-900 hover:bg-black text-white text-[11px] font-bold shadow-sm transition flex items-center gap-1.5 ml-auto">
                                        <i class="fa-solid fa-magnifying-glass"></i> Investigate
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                    <i class="fa-solid fa-shield-check text-emerald-500 text-3xl block mb-2"></i>
                                    Zero flagged referrals found. All accounts have clean risk scores!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Shared Payout Accounts & IP Clusters Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- IP Clusters -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between font-bold text-xs text-gray-900">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-network-wired text-amber-500"></i> Multi-Account IP Clusters</span>
                    <span class="text-[10px] bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full font-bold">&gt;2 Signups from same IP</span>
                </div>

                <div class="space-y-2">
                    @forelse($fraudSignals['ip_clusters'] as $ipc)
                        <div class="flex items-center justify-between text-xs p-3 rounded-2xl bg-amber-50/50 border border-amber-100">
                            <span class="font-mono font-bold text-gray-800">{{ $ipc->ip_address }}</span>
                            <span class="font-extrabold text-amber-700 bg-amber-100/80 px-2.5 py-1 rounded-xl">{{ $ipc->total }} registrations</span>
                        </div>
                    @empty
                        <div class="text-xs text-gray-500 italic p-4 text-center">No multi-account IP clusters detected.</div>
                    @endforelse
                </div>
            </div>

            <!-- Duplicate Bank / UPI Accounts -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between font-bold text-xs text-gray-900">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-red-500"></i> Colliding Payout Accounts</span>
                    <span class="text-[10px] bg-red-100 text-red-800 px-2.5 py-0.5 rounded-full font-bold">Shared by 2+ Distinct Sellers</span>
                </div>

                <div class="space-y-2">
                    @forelse($fraudSignals['duplicate_bank_accounts'] as $dba)
                        <div class="flex items-center justify-between text-xs p-3 rounded-2xl bg-red-50/50 border border-red-100">
                            <span class="font-mono text-gray-800 font-bold">Bank: •••• {{ substr($dba->account_number, -4) }}</span>
                            <span class="font-extrabold text-red-700 bg-red-100/80 px-2.5 py-1 rounded-xl">Shared by {{ $dba->user_count }} sellers</span>
                        </div>
                    @empty
                    @endforelse

                    @forelse($fraudSignals['duplicate_upis'] as $dupi)
                        <div class="flex items-center justify-between text-xs p-3 rounded-2xl bg-red-50/50 border border-red-100">
                            <span class="font-mono text-gray-800 font-bold">UPI: {{ $dupi->upi_id }}</span>
                            <span class="font-extrabold text-red-700 bg-red-100/80 px-2.5 py-1 rounded-xl">Shared by {{ $dupi->user_count }} sellers</span>
                        </div>
                    @empty
                        @if(count($fraudSignals['duplicate_bank_accounts']) === 0)
                            <div class="text-xs text-gray-500 italic p-4 text-center">Zero colliding payout accounts detected across sellers.</div>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Investigation History Audit Trail -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-extrabold text-sm text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-gray-700"></i> Fraud Investigation History Log
                </h3>
                <p class="text-xs text-gray-400">Complete immutable record of all administrative reviews, holds, releases, and rejections</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Timestamp</th>
                            <th class="px-6 py-4">Target Account</th>
                            <th class="px-6 py-4">Action Taken</th>
                            <th class="px-6 py-4">Risk Evaluation</th>
                            <th class="px-6 py-4">Admin Reviewer</th>
                            <th class="px-6 py-4">Investigation Findings</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($fraudSignals['investigation_history'] as $ih)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-mono text-gray-500">{{ $ih->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ $ih->user?->name ?? 'User #' . $ih->user_id }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                        @if($ih->action === 'release') bg-emerald-100 text-emerald-800
                                        @elseif($ih->action === 'reject') bg-red-100 text-red-800
                                        @elseif($ih->action === 'hold') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $ih->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono font-bold text-gray-700">{{ $ih->risk_score }}/100 ({{ strtoupper($ih->risk_level) }})</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-semibold">{{ $ih->admin?->name ?? 'Admin #' . $ih->admin_id }}</td>
                                <td class="px-6 py-4 text-gray-700 max-w-sm truncate">{{ $ih->findings }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">No investigation actions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 7: AUDIT LOGS -->
    <div x-show="activeTab === 'audit'" x-cloak class="space-y-4">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-extrabold text-sm text-gray-900">Admin Referral Audit Logs</h3>
                <p class="text-xs text-gray-400">Immutable chronological record of all administrative financial approvals and rule updates</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Timestamp</th>
                            <th class="px-6 py-4">Admin / Actor</th>
                            <th class="px-6 py-4">Action</th>
                            <th class="px-6 py-4">Target</th>
                            <th class="px-6 py-4">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $log->created_at->format('d M Y, h:i:s A') }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ $log->user ? $log->user->name : 'System' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-gray-100 text-gray-800">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-800">
                                    {{ $log->target_type ? $log->target_type . ' #' . $log->target_id : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-mono text-gray-500">
                                    {{ $log->ip_address ?: 'Local' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">No audit logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL: PAY COMMISSION (SINGLE) -->
    <div x-show="showPayModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showPayModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form :action="'{{ url('admin/referrals/commissions') }}/' + payData.id + '/status'" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="mark_paid">
                    <div class="bg-white p-6 space-y-4">
                        <h3 class="text-lg font-extrabold text-gray-900 border-b border-gray-100 pb-3">Mark Commission as Paid</h3>
                        
                        <div class="p-3 bg-emerald-50 rounded-xl text-xs text-emerald-800 font-semibold space-y-1">
                            <div>Beneficiary: <strong x-text="payData.beneficiary"></strong></div>
                            <div>Payout Amount: <strong class="text-sm">₹<span x-text="payData.amount"></span></strong></div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Bank UTR / Transaction Reference</label>
                            <input type="text" name="payment_reference" required placeholder="e.g. UTR123456789 or NEFT-XXX" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-gray-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Payout Notes (Optional)</label>
                            <textarea name="payout_notes" rows="2" placeholder="e.g. Paid via HDFC Corporate Banking" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-gray-900"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showPayModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: REJECT COMMISSION -->
    <div x-show="showRejectModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showRejectModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form :action="'{{ url('admin/referrals/commissions') }}/' + rejectData.id + '/status'" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <div class="bg-white p-6 space-y-4">
                        <h3 class="text-lg font-extrabold text-red-600 border-b border-gray-100 pb-3">Reject Commission</h3>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Rejection Reason</label>
                            <textarea name="rejection_reason" required rows="3" placeholder="Explain why this commission was rejected..." class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-red-500"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: PROCESS WITHDRAWAL PAYOUT -->
    <div x-show="showPayoutActionModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showPayoutActionModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form :action="'{{ url('admin/referrals/payouts') }}/' + payoutActionData.id + '/process'" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white p-6 space-y-4">
                        <h3 class="text-lg font-extrabold text-gray-900 border-b border-gray-100 pb-3">Disburse Referral Payout</h3>
                        
                        <div class="p-3 bg-indigo-50 rounded-xl text-xs text-indigo-900 font-semibold space-y-1">
                            <div>Seller: <strong x-text="payoutActionData.seller"></strong></div>
                            <div>Payout Amount: <strong class="text-base text-indigo-700">₹<span x-text="payoutActionData.amount"></span></strong></div>
                            <div>Destination: <span class="font-mono text-gray-700" x-text="payoutActionData.destination"></span></div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Action</label>
                            <select name="action" class="w-full px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-bold focus:outline-none focus:border-gray-900">
                                <option value="mark_paid">Mark as Paid (Disbursed)</option>
                                <option value="approve">Approve (Ready for Payment)</option>
                                <option value="mark_processing">Mark Processing</option>
                                <option value="reject">Reject Request</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Bank UTR / Transaction Reference (if paying)</label>
                            <input type="text" name="payment_reference" placeholder="e.g. UTR-9876543210" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-gray-900 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Upload Payment Proof / Receipt (Optional)</label>
                            <input type="file" name="payment_proof" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Notes or Rejection Reason</label>
                            <textarea name="rejection_reason" rows="2" placeholder="Mandatory if rejecting..." class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-gray-900"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showPayoutActionModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Confirm Disbursal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDIT CUSTOM CODE -->
    <div x-show="showCodeModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showCodeModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form :action="'{{ url('admin/referrals/sellers') }}/' + codeData.id + '/code'" method="POST">
                    @csrf
                    <div class="bg-white p-6 space-y-4">
                        <h3 class="text-lg font-extrabold text-gray-900 border-b border-gray-100 pb-3">Update Seller Referral Code</h3>
                        
                        <div class="text-xs text-gray-600">
                            Seller: <strong x-text="codeData.name"></strong>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Custom Referral Code</label>
                            <input type="text" name="custom_code" x-model="codeData.code" placeholder="e.g. OSCBRAND" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-mono uppercase focus:outline-none focus:border-gray-900 font-bold">
                            <span class="text-[10px] text-gray-400">Leave blank to randomly regenerate.</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showCodeModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Save Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: CREATE CAMPAIGN RULE -->
    <div x-show="showRuleModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showRuleModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('admin.referrals.rules.store') }}" method="POST">
                    @csrf
                    <div class="bg-white p-6 space-y-4">
                        <h3 class="text-lg font-extrabold text-gray-900 border-b border-gray-100 pb-3">Create New Referral Rule</h3>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Rule / Campaign Name</label>
                            <input type="text" name="name" required placeholder="e.g. Standard 5% Referral Program" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-gray-900">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Reward Type</label>
                                <select name="reward_type" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                                    <option value="fixed">Fixed Amount (₹)</option>
                                    <option value="percentage">Percentage (%) of Freight</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Referrer Reward (₹)</label>
                                <input type="number" step="0.01" name="reward_value" required value="200.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Referee Bonus (₹)</label>
                                <input type="number" step="0.01" name="referee_reward_value" value="200.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Trigger Event</label>
                                <select name="trigger_event" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                                    <option value="first_wallet_recharge" selected>1st Wallet Recharge (Both Sellers)</option>
                                    <option value="first_delivered_shipment">1st Delivered Order</option>
                                    <option value="every_delivered_shipment">Every Delivered Order</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Min Order Value (₹)</label>
                                <input type="number" step="0.01" name="min_order_value" value="50.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Validity (Days)</label>
                                <input type="number" name="validity_days" value="90" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Max Cap (₹)</label>
                                <input type="number" step="0.01" name="max_commission_per_referral" value="5000.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Min Withdrawal (₹)</label>
                                <input type="number" step="0.01" name="min_withdrawal_amount" value="500.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold">
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-1">
                            <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                <input type="checkbox" name="requires_kyc" value="1" checked class="rounded text-indigo-600">
                                <span>Require Approved KYC</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded text-indigo-600">
                                <span>Set as Active Campaign</span>
                            </label>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showRuleModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Create Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: FRAUD INVESTIGATION & ACTION -->
    <div x-show="showInvestigateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showInvestigateModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('admin.referrals.investigate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="target_type" :value="investigateData.type">
                    <input type="hidden" name="target_id" :value="investigateData.id">

                    <div class="bg-white p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                                <i class="fa-solid fa-shield-virus text-red-600"></i> Fraud Investigation
                            </h3>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-800"
                                x-text="(investigateData.level || 'low').toUpperCase() + ' (' + (investigateData.score || 0) + '/100)'">
                            </span>
                        </div>
                        
                        <div class="p-3 bg-gray-50 rounded-2xl text-xs space-y-1.5 border border-gray-200">
                            <div>Target Seller: <strong class="text-gray-900" x-text="investigateData.seller"></strong></div>
                            <div class="text-[11px] text-gray-500">Record ID: <span class="font-mono" x-text="'#' + investigateData.id + ' (' + investigateData.type + ')'"></span></div>
                        </div>

                        <!-- Detected Risk Signals -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-500">Triggered Heuristic Flags</label>
                            <template x-if="investigateData.factors && investigateData.factors.length > 0">
                                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                    <template x-for="factor in investigateData.factors" :key="factor.code">
                                        <div class="p-2.5 rounded-xl bg-red-50/60 border border-red-100 text-xs flex items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation text-red-500 text-xs mt-0.5"></i>
                                            <div class="flex-1">
                                                <div class="font-bold text-red-900" x-text="factor.code"></div>
                                                <div class="text-[11px] text-red-700" x-text="factor.description"></div>
                                            </div>
                                            <span class="font-mono font-bold text-red-800 text-[10px]" x-text="'+' + factor.points"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!investigateData.factors || investigateData.factors.length === 0">
                                <div class="text-xs text-gray-400 italic p-2 bg-gray-50 rounded-xl">No specific risk signals attached to this record.</div>
                            </template>
                        </div>

                        <!-- Investigation Action Selection -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Administrative Action</label>
                            <select name="action" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold focus:outline-none focus:border-gray-900">
                                <option value="hold">Hold Commission / Referral (Under Investigation)</option>
                                <option value="release">Release & Clear Flag (Verified Legitimate)</option>
                                <option value="reject">Reject & Disqualify (Confirmed Fraudulent)</option>
                                <option value="verify">Mark Verified & Monitored</option>
                            </select>
                        </div>

                        <!-- Investigation Findings -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Investigation Findings & Rationale <span class="text-red-500">*</span></label>
                            <textarea name="findings" required rows="3" placeholder="Document verified phone confirmation, IP check findings, or reason for action..." class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-gray-900"></textarea>
                            <span class="text-[10px] text-gray-400">Recorded permanently in the investigation audit trail.</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showInvestigateModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Apply Investigation Action</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
