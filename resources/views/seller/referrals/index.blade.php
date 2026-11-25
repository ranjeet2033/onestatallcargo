@extends('layouts.seller')

@section('title', 'Refer & Earn - OneStall Cargo')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ request('tab', 'referrals') }}', 
    copied: false, 
    showPayoutMethodModal: false, 
    payoutType: 'bank',
    showWithdrawModal: false,
    showWalletTransferModal: false,
    withdrawAmount: {{ max(0, $stats['available_balance']) }},
    transferAmount: {{ max(0, $stats['available_balance']) }}
}">

    <!-- Flash Messages -->
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
            <div class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-xmark"></i> Please correct the errors below:</div>
            <ul class="list-disc pl-5 font-medium">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Hero Banner with Referral Code & Link -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1e1b4b] via-[#312e81] to-[#4338ca] text-white p-6 sm:p-8 shadow-xl">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2 max-w-xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-emerald-300 border border-white/10 backdrop-blur-md">
                    <i class="fa-solid fa-gift"></i> Seller Referral & Earn Program
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Invite Sellers & Earn Recurring Commissions</h1>
                <p class="text-xs sm:text-sm text-indigo-100 leading-relaxed">
                    Earn commissions directly to your referral ledger for every qualifying delivered shipment sent by sellers who register with your referral link.
                </p>
                @if($activeRule)
                    <div class="inline-flex items-center gap-2 text-xs font-semibold bg-emerald-500/20 text-emerald-300 px-3 py-1.5 rounded-lg border border-emerald-400/20">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Active Reward: 
                            <strong>
                                @if($activeRule->reward_type === 'percentage')
                                    {{ $activeRule->reward_value }}% of shipping freight
                                @else
                                    ₹{{ number_format($activeRule->reward_value, 2) }} flat
                                @endif
                            </strong> 
                            per qualifying delivered order (Min freight: ₹{{ number_format($activeRule->min_order_value) }})
                        </span>
                    </div>
                @endif
            </div>

            <!-- Referral Link Widget -->
            <div class="w-full lg:w-96 bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/15 space-y-3 shrink-0">
                <div class="flex items-center justify-between text-xs font-bold text-indigo-200">
                    <span>YOUR UNIQUE CODE</span>
                    <span class="text-emerald-400 font-mono tracking-widest text-sm uppercase">{{ $stats['referral_code'] }}</span>
                </div>
                
                <div class="relative flex items-center">
                    <input type="text" readonly value="{{ $stats['referral_url'] }}" id="refLinkInput" class="w-full pr-24 pl-3 py-2.5 rounded-xl bg-white/95 text-gray-900 text-xs font-semibold focus:outline-none select-all shadow-inner">
                    <button type="button" @click="navigator.clipboard.writeText('{{ $stats['referral_url'] }}'); copied = true; setTimeout(() => copied = false, 2500)" class="absolute right-1 px-3 py-1.5 bg-[#4338ca] hover:bg-[#3730a3] text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow">
                        <i class="fa-regular fa-copy" x-show="!copied"></i>
                        <i class="fa-solid fa-check text-emerald-300" x-show="copied" x-cloak></i>
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>

                <!-- Action Buttons: WhatsApp & Withdraw -->
                <div class="flex items-center gap-2 pt-1">
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Ship with OneStall Cargo for reliable B2B & B2C express shipping across India. Sign up with my referral link to get started: ' . $stats['referral_url']) }}" target="_blank" class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow">
                        <i class="fa-brands fa-whatsapp text-sm"></i> Share WhatsApp
                    </a>
                    <button type="button" @click="activeTab = 'payouts'; showWithdrawModal = true" class="py-2 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-900 font-black text-xs transition shadow flex items-center gap-1">
                        <i class="fa-solid fa-money-bill-transfer"></i> Withdraw
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Detailed Metrics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <!-- 1. Total Referrals -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500">Total Referrals</span>
                <i class="fa-solid fa-users text-blue-500"></i>
            </div>
            <div class="text-2xl font-black text-gray-900">{{ number_format($stats['total_referred']) }}</div>
            <div class="text-[10px] text-gray-400">Total invited sellers</div>
        </div>

        <!-- 2. Registered Sellers -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500">Registered Sellers</span>
                <i class="fa-solid fa-user-check text-indigo-500"></i>
            </div>
            <div class="text-2xl font-black text-indigo-600">{{ number_format($stats['total_referred']) }}</div>
            <div class="text-[10px] text-gray-400">Signups attributed</div>
        </div>

        <!-- 3. KYC-Approved Referrals -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500">KYC-Approved</span>
                <i class="fa-solid fa-id-card-clip text-emerald-500"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['kyc_approved_referrals']) }}</div>
            <div class="text-[10px] text-gray-400">Verified seller accounts</div>
        </div>

        <!-- 4. Eligible Shipments -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500">Eligible Orders</span>
                <i class="fa-solid fa-box text-cyan-600"></i>
            </div>
            <div class="text-2xl font-black text-cyan-700">{{ number_format($stats['eligible_shipments']) }}</div>
            <div class="text-[10px] text-gray-400">Qualified delivered parcels</div>
        </div>

        <!-- 5. Pending Commission -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-500">Pending Review</span>
                <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
            </div>
            <div class="text-2xl font-black text-amber-600">₹{{ number_format($stats['pending_commission'], 2) }}</div>
            <div class="text-[10px] text-gray-400">Under verification or review</div>
        </div>

        <!-- 6. Approved Commission -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-500">Approved Commission</span>
                <i class="fa-solid fa-badge-check text-indigo-500"></i>
            </div>
            <div class="text-2xl font-black text-indigo-600">₹{{ number_format($stats['approved_commission'], 2) }}</div>
            <div class="text-[10px] text-gray-400">Approved & payable earnings</div>
        </div>

        <!-- 7. Paid Commission -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600">Paid Commission</span>
                <i class="fa-solid fa-circle-dollar-to-slot text-emerald-600"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600">₹{{ number_format($stats['paid_commission'], 2) }}</div>
            <div class="text-[10px] text-gray-400">Successfully settled</div>
        </div>

        <!-- 8. Available Payable Balance -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 p-4 sm:p-5 rounded-2xl border border-emerald-200 shadow-sm space-y-2 flex flex-col justify-between">
            <div class="space-y-1">
                <div class="flex items-center justify-between text-emerald-700">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider">Available Balance</span>
                    <i class="fa-solid fa-wallet text-emerald-600"></i>
                </div>
                <div class="text-2xl font-black text-emerald-700">₹{{ number_format($stats['available_balance'], 2) }}</div>
                <div class="text-[10px] text-emerald-600 font-semibold">Min bank payout: ₹{{ number_format($stats['min_withdrawal']) }}</div>
            </div>
            @if($stats['available_balance'] > 0)
                <button type="button" @click="showWalletTransferModal = true" class="w-full py-1.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] transition shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-bolt"></i> Transfer to Shipping Wallet
                </button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-3 border-b border-gray-200">
        <button type="button" @click="activeTab = 'referrals'" :class="activeTab === 'referrals' ? 'border-[#4338ca] text-[#4338ca] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-2">
            <i class="fa-solid fa-users"></i> Referral History ({{ $referrals->total() }})
        </button>
        <button type="button" @click="activeTab = 'ledger'" :class="activeTab === 'ledger' ? 'border-[#4338ca] text-[#4338ca] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-2">
            <i class="fa-solid fa-receipt"></i> Commission Ledger ({{ $commissions->total() }})
        </button>
        <button type="button" @click="activeTab = 'payouts'" :class="activeTab === 'payouts' ? 'border-[#4338ca] text-[#4338ca] font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'" class="pb-3 text-xs uppercase tracking-wider border-b-2 transition flex items-center gap-2">
            <i class="fa-solid fa-building-columns"></i> Payout & Withdrawals ({{ $payoutRequests->total() }})
        </button>
    </div>

    <!-- TAB 1: REFERRAL HISTORY WITH SEARCH & FILTERS -->
    <div x-show="activeTab === 'referrals'" class="space-y-4">
        <!-- Search & Filter Controls -->
        <form action="{{ route('seller.referrals') }}" method="GET" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <input type="hidden" name="tab" value="referrals">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" name="ref_search" value="{{ request('ref_search') }}" placeholder="Search company or email..." class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs w-60 focus:outline-none focus:border-indigo-600">
                
                <select name="ref_status" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600">
                    <option value="">All Verification Statuses</option>
                    <option value="registered" {{ request('ref_status') === 'registered' ? 'selected' : '' }}>Registered</option>
                    <option value="kyc_approved" {{ request('ref_status') === 'kyc_approved' ? 'selected' : '' }}>KYC Approved</option>
                    <option value="active" {{ request('ref_status') === 'active' ? 'selected' : '' }}>Active Shipper</option>
                    <option value="disqualified" {{ request('ref_status') === 'disqualified' ? 'selected' : '' }}>Disqualified</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">
                    Filter
                </button>
                @if(request('ref_search') || request('ref_status'))
                    <a href="{{ route('seller.referrals', ['tab' => 'referrals']) }}" class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-900">Reset</a>
                @endif
            </div>
        </form>

        <!-- Referrals Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Referred Seller</th>
                            <th class="px-6 py-4">Joined Date</th>
                            <th class="px-6 py-4">KYC Status</th>
                            <th class="px-6 py-4">Shipper Status</th>
                            <th class="px-6 py-4">Orders Rewarded</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($referrals as $ref)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $ref->referred->company_name ?: $ref->referred->name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $ref->referred->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $ref->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($ref->referred->isKycApproved())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <i class="fa-solid fa-circle-check"></i> Approved
                                        </span>
                                    @elseif($ref->referred->kyc && $ref->referred->kyc->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                            <i class="fa-solid fa-clock"></i> Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-50 text-gray-500 border border-gray-100">
                                            Not Submitted
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($ref->status === 'active' || $ref->status === 'completed')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Active Shipper</span>
                                    @elseif($ref->status === 'disqualified')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100" title="{{ $ref->disqualified_reason }}">Disqualified</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Registered</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900">{{ $ref->commissions_count }} event(s)</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <i class="fa-solid fa-user-group text-3xl text-gray-200"></i>
                                        <p class="font-bold text-gray-600 text-sm">No Referred Sellers Found</p>
                                        <p class="text-xs">Share your referral link on WhatsApp or invite sellers directly.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($referrals->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $referrals->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: COMMISSION LEDGER WITH SEARCH, STATUS & REJECTION REASONS -->
    <div x-show="activeTab === 'ledger'" x-cloak class="space-y-4">
        <!-- Search & Filter Controls -->
        <form action="{{ route('seller.referrals') }}" method="GET" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <input type="hidden" name="tab" value="ledger">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" name="comm_search" value="{{ request('comm_search') }}" placeholder="Search AWB or Seller..." class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs w-60 focus:outline-none focus:border-indigo-600">
                
                <select name="comm_status" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600">
                    <option value="">All Commission Statuses</option>
                    <option value="pending" {{ request('comm_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="under_review" {{ request('comm_status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('comm_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="payable" {{ request('comm_status') === 'payable' ? 'selected' : '' }}>Payable</option>
                    <option value="paid" {{ request('comm_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('comm_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">
                    Filter
                </button>
                @if(request('comm_search') || request('comm_status'))
                    <a href="{{ route('seller.referrals', ['tab' => 'ledger']) }}" class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-900">Reset</a>
                @endif
            </div>
        </form>

        <!-- Ledger Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Entry / Date</th>
                            <th class="px-6 py-4">Referred Shipper</th>
                            <th class="px-6 py-4">Shipment (AWB)</th>
                            <th class="px-6 py-4">Freight Base</th>
                            <th class="px-6 py-4">Commission</th>
                            <th class="px-6 py-4">Status & Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($commissions as $comm)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">#COMM-{{ $comm->id }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $comm->created_at->format('d M Y, h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $comm->referred->company_name ?: $comm->referred->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $comm->referred->email }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-indigo-600">
                                    {{ $comm->shipment ? $comm->shipment->awb_number : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    ₹{{ number_format($comm->base_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 font-extrabold text-emerald-600 text-sm">
                                    +₹{{ number_format($comm->commission_amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @switch($comm->status)
                                            @case('approved')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    Approved
                                                </span>
                                                @break
                                            @case('payable')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    Payable
                                                </span>
                                                @break
                                            @case('paid')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                                                    Paid ({{ $comm->payment_reference ?: 'Settled' }})
                                                </span>
                                                @break
                                            @case('under_review')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                    Under Review
                                                </span>
                                                @break
                                            @case('rejected')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100">
                                                    Rejected
                                                </span>
                                                @break
                                            @default
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                                    Pending
                                                </span>
                                        @endswitch

                                        @if($comm->rejection_reason)
                                            <span class="text-[11px] text-gray-500 italic max-w-xs truncate" title="{{ $comm->rejection_reason }}">
                                                ({{ $comm->rejection_reason }})
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium">
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

    <!-- TAB 3: PAYOUT SYSTEM & WITHDRAWALS -->
    <div x-show="activeTab === 'payouts'" x-cloak class="space-y-6">
        
        <!-- Payout Overview Card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Balance & Action -->
            <div class="bg-gradient-to-br from-[#1e1b4b] to-[#312e81] text-white p-6 rounded-3xl shadow-lg space-y-4">
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Available Referral Balance</span>
                    <div class="text-3xl font-black text-emerald-400">₹{{ number_format($stats['available_balance'], 2) }}</div>
                    <p class="text-xs text-indigo-200">
                        Minimum withdrawal limit: <strong>₹{{ number_format($stats['min_withdrawal'], 2) }}</strong>
                    </p>
                </div>

                <div class="pt-2 space-y-2">
                    @if($stats['available_balance'] > 0)
                        <button type="button" @click="showWalletTransferModal = true" class="w-full py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs transition flex items-center justify-center gap-2 shadow-md">
                            <i class="fa-solid fa-bolt"></i> Transfer to Shipping Wallet (Instant)
                        </button>
                    @endif

                    @if($stats['available_balance'] >= $stats['min_withdrawal'])
                        <button type="button" @click="showWithdrawModal = true" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-900 font-extrabold text-xs transition flex items-center justify-center gap-2 shadow-md">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Request Bank/UPI Payout
                        </button>
                    @else
                        <button disabled class="w-full py-2.5 px-4 rounded-xl bg-white/10 text-white/40 font-bold text-xs cursor-not-allowed">
                            <i class="fa-solid fa-lock"></i> Bank Min ₹{{ number_format($stats['min_withdrawal']) }} Required
                        </button>
                    @endif
                </div>
            </div>

            <!-- Saved Payout Destination -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4 md:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm text-gray-900">Configured Payout Accounts</h3>
                        <p class="text-xs text-gray-400">Bank accounts and UPI IDs for direct commission payouts</p>
                    </div>
                    <button type="button" @click="showPayoutMethodModal = true" class="px-3.5 py-1.5 rounded-xl border border-gray-200 hover:border-gray-900 text-xs font-bold transition flex items-center gap-1.5 text-gray-700">
                        <i class="fa-solid fa-plus text-xs"></i> Add Account / UPI
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    @forelse($payoutMethods as $m)
                        <div class="p-4 rounded-2xl border {{ $m->is_default ? 'border-indigo-400/50 bg-indigo-50/30' : 'border-gray-100 bg-gray-50/50' }} space-y-2 relative">
                            @if($m->is_default)
                                <span class="absolute top-3 right-3 text-[9px] font-black uppercase tracking-wider bg-indigo-600 text-white px-2 py-0.5 rounded-full">Default</span>
                            @endif
                            <div class="flex items-center gap-2 font-bold text-xs text-gray-900">
                                @if($m->payout_type === 'upi')
                                    <i class="fa-solid fa-mobile-screen-button text-emerald-600"></i> UPI Account
                                @else
                                    <i class="fa-solid fa-building-columns text-indigo-600"></i> {{ $m->bank_name ?: 'Bank Account' }}
                                @endif
                            </div>
                            <div class="text-xs font-mono font-bold text-gray-800">
                                {{ $m->masked_account }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                Holder: <strong>{{ $m->account_holder_name }}</strong>
                                @if($m->ifsc_code) &bull; IFSC: <strong class="uppercase font-mono">{{ $m->ifsc_code }}</strong> @endif
                            </div>
                        </div>
                    @empty
                        <div class="sm:col-span-2 p-6 rounded-2xl border border-dashed border-gray-200 text-center space-y-2 text-gray-400">
                            <i class="fa-solid fa-building-columns text-2xl text-gray-300"></i>
                            <p class="text-xs font-semibold text-gray-600">No payout method added yet.</p>
                            <p class="text-[11px]">Add your Bank Account or UPI ID to enable withdrawal requests.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Withdrawal Requests History Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900">Withdrawal Request History</h3>
                    <p class="text-xs text-gray-400">Real-time tracking of your withdrawal payouts and bank references</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-gray-50/75 text-[10px] uppercase tracking-wider font-extrabold text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Request ID / Date</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Destination Account</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Bank Reference / UTR</th>
                            <th class="px-6 py-4">Notes / Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium text-gray-700">
                        @forelse($payoutRequests as $req)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">#PAYOUT-{{ $req->id }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $req->created_at->format('d M Y, h:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 font-black text-gray-900 text-sm">
                                    ₹{{ number_format($req->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-gray-800 font-medium">
                                    {{ $req->method_display }}
                                </td>
                                <td class="px-6 py-4">
                                    @switch($req->status)
                                        @case('paid')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <i class="fa-solid fa-circle-check"></i> Paid
                                            </span>
                                            @break
                                        @case('approved')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                Approved
                                            </span>
                                            @break
                                        @case('processing')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                <i class="fa-solid fa-spinner fa-spin text-[9px]"></i> Processing
                                            </span>
                                            @break
                                        @case('rejected')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100">
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
                                    @if($req->payment_reference)
                                        <span class="text-emerald-700">{{ $req->payment_reference }}</span>
                                    @else
                                        <span class="text-gray-400 font-normal italic">Pending Disbursement</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    @if($req->rejection_reason)
                                        <span class="text-red-600 font-semibold">{{ $req->rejection_reason }}</span>
                                    @elseif($req->admin_notes)
                                        <span>{{ $req->admin_notes }}</span>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    No withdrawal requests yet.
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

    <!-- MODAL: ADD PAYOUT METHOD -->
    <div x-show="showPayoutMethodModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showPayoutMethodModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('seller.referrals.payout-methods.store') }}" method="POST">
                    @csrf
                    <div class="bg-white p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-lg font-extrabold text-gray-900">Add Payout Account</h3>
                            <button type="button" @click="showPayoutMethodModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <!-- Type Selector (Bank vs UPI) -->
                        <div class="grid grid-cols-2 gap-3 p-1 bg-gray-100 rounded-xl">
                            <button type="button" @click="payoutType = 'bank'" :class="payoutType === 'bank' ? 'bg-white shadow text-gray-900 font-black' : 'text-gray-600 font-semibold'" class="py-2 rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-building-columns"></i> Bank Account
                            </button>
                            <button type="button" @click="payoutType = 'upi'" :class="payoutType === 'upi' ? 'bg-white shadow text-gray-900 font-black' : 'text-gray-600 font-semibold'" class="py-2 rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-mobile-screen-button"></i> UPI ID
                            </button>
                        </div>
                        <input type="hidden" name="payout_type" :value="payoutType">

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Account Holder Name</label>
                            <input type="text" name="account_holder_name" required value="{{ $user->name }}" placeholder="As per bank / PAN record" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600">
                        </div>

                        <!-- Bank Account Fields -->
                        <div x-show="payoutType === 'bank'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Bank Name</label>
                                <input type="text" name="bank_name" placeholder="e.g. HDFC Bank, ICICI Bank, SBI" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Account Number</label>
                                    <input type="text" name="account_number" placeholder="9-18 digit account" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600 font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">IFSC Code</label>
                                    <input type="text" name="ifsc_code" placeholder="HDFC0001234" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600 font-mono uppercase">
                                </div>
                            </div>
                        </div>

                        <!-- UPI Field -->
                        <div x-show="payoutType === 'upi'" style="display: none;" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">UPI ID (VPA)</label>
                                <input type="text" name="upi_id" placeholder="e.g. mobile@paytm, user@okhdfcbank" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600">
                            </div>
                        </div>

                        <div class="pt-1">
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" checked class="rounded text-indigo-600">
                                <span>Set as default payout account</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showPayoutMethodModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: REQUEST WITHDRAWAL -->
    <div x-show="showWithdrawModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showWithdrawModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form action="{{ route('seller.referrals.withdraw') }}" method="POST">
                    @csrf
                    <div class="bg-white p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-lg font-extrabold text-gray-900">Request Referral Payout</h3>
                            <button type="button" @click="showWithdrawModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 space-y-1">
                            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Available Referral Balance</div>
                            <div class="text-2xl font-black text-emerald-700">₹{{ number_format($stats['available_balance'], 2) }}</div>
                            <div class="text-[10px] text-emerald-600">Minimum withdrawal: ₹{{ number_format($stats['min_withdrawal'], 2) }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Withdrawal Amount (₹)</label>
                            <input type="number" step="0.01" min="{{ $stats['min_withdrawal'] }}" max="{{ $stats['available_balance'] }}" name="amount" x-model="withdrawAmount" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-black focus:outline-none focus:border-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Select Payout Destination</label>
                            <select name="payout_method_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-indigo-600">
                                @forelse($payoutMethods as $pm)
                                    <option value="{{ $pm->id }}" {{ $pm->is_default ? 'selected' : '' }}>
                                        {{ $pm->payout_type === 'upi' ? 'UPI: ' . $pm->upi_id : ($pm->bank_name . ' - ' . $pm->masked_account) }}
                                    </option>
                                @empty
                                    <option value="">No account added (Add one first)</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showWithdrawModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-900 text-xs font-black transition">Submit Withdrawal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: TRANSFER TO SHIPPING WALLET -->
    <div x-show="showWalletTransferModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 opacity-75" @click="showWalletTransferModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form action="{{ route('seller.referrals.transfer-wallet') }}" method="POST">
                    @csrf
                    <div class="bg-white p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-bolt text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-gray-900">Transfer to Shipping Wallet</h3>
                                    <p class="text-[11px] text-gray-400">Convert referral earnings to parcel booking balance</p>
                                </div>
                            </div>
                            <button type="button" @click="showWalletTransferModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border border-emerald-100 space-y-2">
                            <div>
                                <div class="text-[10px] font-extrabold text-emerald-800 uppercase tracking-wider">Available Referral Balance</div>
                                <div class="text-2xl font-black text-emerald-700">₹{{ number_format($stats['available_balance'], 2) }}</div>
                            </div>
                            <div class="text-[11px] text-gray-600 flex items-center justify-between pt-2 border-t border-emerald-100 font-medium">
                                <span>Current Shipping Recharge Wallet:</span>
                                <span class="font-bold text-gray-900">₹{{ number_format($user->wallet_balance ?? 0, 2) }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Transfer Amount (₹)</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $stats['available_balance'] }}" name="amount" x-model="transferAmount" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-base font-black text-emerald-700 focus:outline-none focus:border-emerald-600 font-mono">
                            <p class="text-[11px] text-gray-400 mt-1.5 leading-relaxed">
                                Funds will instantly be credited to your Shipping Recharge balance and can be used immediately to book shipments.
                            </p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showWalletTransferModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-bolt"></i> Transfer Instantly
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
