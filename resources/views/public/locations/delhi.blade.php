@extends('layouts.public')

@section('title', 'Courier & Cargo Service in Delhi NCR | OneStall Central Hub Okhla')
@section('meta_description', 'OneStall Cargo Delhi Central Hub in Okhla provides express B2C parcel delivery, B2B cargo distribution, and same-day pickups across Delhi, Noida, and Gurugram.')
@section('meta_keywords', 'Courier Service in Delhi NCR, Delhi Cargo Hub, Okhla Logistics Center, Courier Service in South Delhi, Express Parcel Delivery Delhi')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-500">Locations</span>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Delhi NCR Central Hub</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode(seo()->localBusinessSchema([
    'name' => 'OneStall Cargo Delhi Central Logistics Hub',
    'address' => 'Warehouse B-12, Okhla Industrial Area, Phase 1',
    'city' => 'New Delhi',
    'state' => 'Delhi',
    'pincode' => '110020',
    'phone' => '+91-1800-123-4567',
    'email' => 'hub@onestallcargo.com',
    'url' => url('/locations/delhi-ncr')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Locations' => route('services'),
    'Delhi NCR Central Hub' => url('/locations/delhi-ncr')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-50/40 via-white to-blue-50/40 py-12 md:py-16 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2 text-center lg:text-left">
                <div class="inline-block px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-brand-navy text-xs font-bold mb-4 uppercase tracking-wide">
                    Northern India Master Sorting Hub
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-brand-navy leading-tight mb-4">
                    Express Courier &amp; Cargo Hub in <span class="text-brand-red">Delhi NCR</span>
                </h1>
                <p class="text-base md:text-lg text-gray-700 mb-8 font-medium leading-relaxed">
                    Operating from Okhla Industrial Area, the OneStall Delhi Central Hub powers rapid last-mile deliveries, bulk B2B freight consolidation, and same-day warehouse pickups across Delhi, Noida, Greater Noida, Gurugram, and Faridabad.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-full bg-brand-red text-white font-bold text-base hover:bg-brand-redHover transition shadow-md shadow-red-500/20">
                        Schedule Delhi Pickup
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-3.5 rounded-full border-2 border-brand-navy text-brand-navy font-bold text-base hover:bg-brand-navy hover:text-white transition">
                        Contact Delhi Hub
                    </a>
                </div>
            </div>

            <!-- Hub Details Card -->
            <div class="w-full lg:w-1/2">
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-200 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-brand-navy flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-warehouse"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-brand-red uppercase">Verified Operational Facility</span>
                            <h2 class="text-xl font-extrabold text-gray-900">Delhi Central Hub (DEL-01)</h2>
                        </div>
                    </div>

                    <div class="space-y-4 text-sm text-gray-700">
                        <div class="flex items-start space-x-3">
                            <i class="fa-solid fa-location-dot text-brand-red mt-1 text-base"></i>
                            <div>
                                <strong class="text-gray-900 block">Hub Address:</strong>
                                <span>Warehouse B-12, Okhla Industrial Area, Phase 1, South Delhi, New Delhi – 110020</span>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fa-solid fa-clock text-brand-navy mt-1 text-base"></i>
                            <div>
                                <strong class="text-gray-900 block">Operating Hours:</strong>
                                <span>Monday to Saturday: 08:00 AM – 08:00 PM (Sunday Dispatch on Priority)</span>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fa-solid fa-truck-fast text-brand-navy mt-1 text-base"></i>
                            <div>
                                <strong class="text-gray-900 block">Core Capabilities:</strong>
                                <span>Same-day NCR pickups, 2-hour hyperlocal bike dispatch, B2B LTL/FTL truck consolidation, and packaging video inspection.</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-2 text-xs font-semibold text-gray-600">
                        <span class="bg-gray-100 px-3 py-1 rounded-full">110001 (Connaught Place)</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">110020 (Okhla)</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">110025 (Jamia Nagar)</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">110044 (Badarpur)</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">110065 (Nehru Place)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NCR Service Capabilities -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-navy mb-3">Logistics Solutions for Delhi NCR Businesses</h2>
            <p class="text-sm md:text-base text-gray-600">Tailored for online brands, Chandni Chowk wholesalers, Okhla manufacturers, and Gurugram corporate offices.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-2xl text-brand-red mb-3"><i class="fa-solid fa-box"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">D2C eCommerce Shipping</h3>
                <p class="text-xs text-gray-600 leading-relaxed mb-4">Direct doorstep parcel delivery to all Delhi NCR residential sectors with automated COD remittance in 1-2 business days.</p>
                <a href="{{ route('solutions.b2c') }}" class="text-xs font-bold text-brand-navy hover:underline">Learn more &rarr;</a>
            </div>

            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-2xl text-brand-red mb-3"><i class="fa-solid fa-truck-moving"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">B2B Heavy Cargo & Freight</h3>
                <p class="text-xs text-gray-600 leading-relaxed mb-4">LTL and full truckload freight linking Delhi industrial corridors to Mumbai, Kolkata, Bengaluru, and Chennai at discounted rates.</p>
                <a href="{{ route('solutions.b2b') }}" class="text-xs font-bold text-brand-navy hover:underline">Learn more &rarr;</a>
            </div>

            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-2xl text-brand-red mb-3"><i class="fa-solid fa-store"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delhi NCR Franchise Hubs</h3>
                <p class="text-xs text-gray-600 leading-relaxed mb-4">Interested in opening a local booking counter or sorting hub in Delhi? Explore our high-ROI franchise partner program.</p>
                <a href="{{ route('franchise') }}" class="text-xs font-bold text-brand-navy hover:underline">Apply for franchise &rarr;</a>
            </div>
        </div>
    </div>
</section>
@endsection
