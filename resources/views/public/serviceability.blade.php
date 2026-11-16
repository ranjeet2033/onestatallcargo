@extends('layouts.public')

@section('title', 'Check Courier Serviceability & Pincodes | OneStall Cargo')
@section('meta_description', 'Check courier serviceability and COD availability across 29,000+ Indian pincodes. Verify delivery coverage across metro, tier-2, and remote regions.')
@section('meta_keywords', 'Courier Pincode Checker India, Deliverable Pincodes India, Courier Serviceability Search, Cash on Delivery Pincode List, OneStall Pincode Network')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Pincode Serviceability</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => 'OneStall Courier Pincode Serviceability Checker',
    'applicationCategory' => 'BusinessApplication',
    'description' => 'Real-time pincode serviceability and Cash on Delivery (COD) verification tool across 29,000+ Indian postal codes.'
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Serviceability' => url('/serviceability')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-50/40 via-white to-blue-50/40 py-12 md:py-16 relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-block px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-brand-navy text-xs font-bold mb-4 uppercase tracking-wide">
            Nationwide Coverage Database
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold text-brand-navy leading-tight mb-4">
            Check Courier Serviceability Across <span class="text-brand-red">29,000+ Pin Codes</span>
        </h1>
        <p class="text-base md:text-lg text-gray-600 mb-8 font-medium max-w-2xl mx-auto">
            Verify express door delivery, Cash on Delivery (COD), and B2B cargo coverage for any 6-digit postal code in India.
        </p>

        <!-- Search Form -->
        <form action="{{ url('/serviceability') }}" method="GET" class="max-w-xl mx-auto mb-6">
            <div class="relative flex items-center bg-white rounded-full border border-gray-300 shadow-md p-1.5 focus-within:border-brand-navy transition">
                <i class="fa-solid fa-map-pin text-gray-400 pl-4 pr-2 text-base"></i>
                <input type="text" name="pincode" value="{{ $pincodeQuery ?? '' }}" maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit Pincode (e.g. 110001, 400001, 226001)..." class="w-full text-sm font-semibold text-gray-800 bg-transparent focus:outline-none placeholder-gray-400" required>
                <button type="submit" class="px-7 py-3 rounded-full bg-brand-red text-white font-bold text-xs hover:bg-brand-redHover transition shadow-sm whitespace-nowrap">
                    Check Pincode
                </button>
            </div>
        </form>

        <!-- Search Result Alert -->
        @if(isset($result))
        <div class="max-w-xl mx-auto mt-6">
            @if($result['is_serviceable'])
            <div class="bg-green-50 border-2 border-green-400 text-green-900 rounded-2xl p-6 text-left shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base">Pincode {{ $result['pincode'] }} is 100% Serviceable</h3>
                        <p class="text-xs text-green-700">Covered by OneStall Cargo multi-carrier network</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-green-200 text-xs">
                    <div><strong>Prepaid Express:</strong> <span class="text-green-800 font-semibold">&check; Available (Air &amp; Surface)</span></div>
                    <div><strong>Cash on Delivery (COD):</strong> <span class="text-green-800 font-semibold">&check; Available (1-2 Day Remit)</span></div>
                    <div><strong>Doorstep Pickup:</strong> <span class="text-green-800 font-semibold">&check; Supported</span></div>
                    <div><strong>B2B Heavy Cargo:</strong> <span class="text-green-800 font-semibold">&check; LTL &amp; FTL Supported</span></div>
                </div>
                <div class="mt-4 pt-3 border-t border-green-200 text-center">
                    <a href="{{ route('register') }}" class="inline-block px-6 py-2 rounded-full bg-green-700 text-white font-bold text-xs hover:bg-green-800 transition">
                        Start Shipping to {{ $result['pincode'] }} &rarr;
                    </a>
                </div>
            </div>
            @else
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-2xl p-6 text-left shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>
                    <div>
                        <h3 class="font-bold text-sm">Invalid Pincode Format</h3>
                        <p class="text-xs text-red-600">Please enter a valid 6-digit Indian postal code (e.g. 110020, 226023, 560001).</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</section>

<!-- Coverage Highlights Grid -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-navy mb-3">Extensive Coverage Across India</h2>
            <p class="text-sm md:text-base text-gray-600">Delivering to every corner of India through our integrated 15+ carrier infrastructure.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-3xl font-black text-brand-navy mb-1">29,000+</div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">Postal Pincodes</h4>
                <p class="text-xs text-gray-500">Tier-1 metros, Tier-2/3 smart cities, and rural postal zones.</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-3xl font-black text-brand-red mb-1">15+</div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">Integrated Couriers</h4>
                <p class="text-xs text-gray-500">Delhivery, BlueDart, XpressBees, Shadowfax &amp; more.</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-3xl font-black text-brand-navy mb-1">99.4%</div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">COD Availability</h4>
                <p class="text-xs text-gray-500">Cash on delivery collection supported in over 27,000 pincodes.</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-3xl font-black text-brand-navy mb-1">1-2 Days</div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">COD Remittance</h4>
                <p class="text-xs text-gray-500">Rapid bank settlement to keep business cash flow uninterrupted.</p>
            </div>
        </div>
    </div>
</section>
@endsection
