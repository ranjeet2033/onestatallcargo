@extends('layouts.public')

@section('title', 'Courier & Cargo Services in Lucknow, UP | OneStall Headquarters')
@section('meta_description', 'OneStall Cargo registered office and central Uttar Pradesh distribution hub in Lucknow. Seamless parcel delivery and bulk cargo across 75 UP districts.')
@section('meta_keywords', 'Courier Service in Lucknow UP, Lucknow Logistics Hub, Uttar Pradesh Cargo Transport, Courier Company Lucknow, Registered Office UP')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-500">Locations</span>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Lucknow Corporate HQ &amp; Hub</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode(seo()->localBusinessSchema([
    'name' => 'OneStall Cargo Corporate Headquarters & Central UP Hub',
    'address' => '569/153 KHA, Barigawan, KP Road, 32 BN PAC',
    'city' => 'Lucknow',
    'state' => 'Uttar Pradesh',
    'pincode' => '226023',
    'phone' => '+91-1800-123-4567',
    'email' => 'support@onestallcargo.com',
    'url' => url('/locations/lucknow-up')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Locations' => route('services'),
    'Lucknow Headquarters & Hub' => url('/locations/lucknow-up')
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
                    Corporate Headquarters &amp; Central UP Hub
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-brand-navy leading-tight mb-4">
                    Courier &amp; Cargo Hub in <span class="text-brand-red">Lucknow, Uttar Pradesh</span>
                </h1>
                <p class="text-base md:text-lg text-gray-700 mb-8 font-medium leading-relaxed">
                    Headquartered in Lucknow, OneStall Cargo (Anyurva Ayurveda Private Limited) coordinates extensive statewide supply chain logistics, eCommerce fulfillment, and heavy freight distribution connecting Lucknow and Kanpur to all 75 districts of Uttar Pradesh.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-full bg-brand-red text-white font-bold text-base hover:bg-brand-redHover transition shadow-md shadow-red-500/20">
                        Book Shipment from Lucknow
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-3.5 rounded-full border-2 border-brand-navy text-brand-navy font-bold text-base hover:bg-brand-navy hover:text-white transition">
                        Contact Lucknow Office
                    </a>
                </div>
            </div>

            <!-- Headquarters Facility Card -->
            <div class="w-full lg:w-1/2">
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-200 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="w-12 h-12 rounded-xl bg-red-100 text-brand-red flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-brand-red uppercase">Registered Corporate Office</span>
                            <h2 class="text-xl font-extrabold text-gray-900">ANYURVA AYURVEDA PRIVATE LIMITED</h2>
                            <p class="text-xs text-gray-400 font-mono">CIN: U21003UP2024PTC205462</p>
                        </div>
                    </div>

                    <div class="space-y-4 text-sm text-gray-700">
                        <div class="flex items-start space-x-3">
                            <i class="fa-solid fa-location-dot text-brand-red mt-1 text-base"></i>
                            <div>
                                <strong class="text-gray-900 block">Office Address:</strong>
                                <span>569/153 KHA, Barigawan, KP Road, 32 BN PAC, Lucknow – 226023, Uttar Pradesh, India</span>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fa-solid fa-phone text-brand-navy mt-1 text-base"></i>
                            <div>
                                <strong class="text-gray-900 block">Direct Inquiries:</strong>
                                <span>1800-123-4567 &bull; support@onestallcargo.com</span>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fa-solid fa-truck-moving text-brand-navy mt-1 text-base"></i>
                            <div>
                                <strong class="text-gray-900 block">Statewide Coverage:</strong>
                                <span>Express parcel distribution, B2B commercial cargo, state franchise management, and rural pin code last-mile routing.</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-2 text-xs font-semibold text-gray-600">
                        <span class="bg-gray-100 px-3 py-1 rounded-full">Lucknow (226001 - 226030)</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">Kanpur Hubs</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">Varanasi Line-haul</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">Agra Commercial</span>
                        <span class="bg-gray-100 px-3 py-1 rounded-full">75 UP Districts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- UP Services Grid -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-navy mb-3">Freight &amp; Courier Coverage in Uttar Pradesh</h2>
            <p class="text-sm md:text-base text-gray-600">Connecting central UP manufacturing hubs to national consumer markets.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-2xl text-brand-red mb-3"><i class="fa-solid fa-box"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">UP E-Commerce Distribution</h3>
                <p class="text-xs text-gray-600 leading-relaxed mb-4">Doorstep delivery across all cities and rural tehsils in Uttar Pradesh with Cash on Delivery and 1-2 day fast remittance.</p>
                <a href="{{ route('solutions.b2c') }}" class="text-xs font-bold text-brand-navy hover:underline">Explore B2C Shipping &rarr;</a>
            </div>

            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-2xl text-brand-red mb-3"><i class="fa-solid fa-truck-moving"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">UP Industrial Freight &amp; Cargo</h3>
                <p class="text-xs text-gray-600 leading-relaxed mb-4">Full Truckload (FTL) and LTL cargo connecting Lucknow, Kanpur, Unnao, and Meerut industrial estates with GST E-Way Bill support.</p>
                <a href="{{ route('solutions.b2b') }}" class="text-xs font-bold text-brand-navy hover:underline">Explore B2B Cargo &rarr;</a>
            </div>

            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                <div class="text-2xl text-brand-red mb-3"><i class="fa-solid fa-store"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Uttar Pradesh Franchise Program</h3>
                <p class="text-xs text-gray-600 leading-relaxed mb-4">Partner with OneStall Cargo to open exclusive district booking counters or sorting hubs across UP cities.</p>
                <a href="{{ route('franchise') }}" class="text-xs font-bold text-brand-navy hover:underline">Apply for Franchise &rarr;</a>
            </div>
        </div>
    </div>
</section>
@endsection
