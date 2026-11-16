@extends('layouts.public')

@section('title', 'Shipping Company in India | Express Courier & Cargo Services | OneStall')
@section('meta_description', 'Discover all services from OneStall Cargo, India\'s trusted shipping company. B2C express courier, B2B freight, international cargo, and franchise hub logistics.')
@section('meta_keywords', 'Shipping Company in India, Express Courier Services, B2B Cargo Services, Domestic Freight Transport, Warehousing Logistics')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Services</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode(seo()->serviceSchema([
    'name' => 'OneStall Cargo Integrated Logistics & Courier Services',
    'type' => 'Express Courier, Freight Cargo & Logistics Aggregator',
    'areaServed' => 'India',
    'description' => 'Comprehensive logistics solutions for eCommerce, B2B cargo, international courier, hyperlocal delivery, and franchise networks in India.'
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Services' => route('services')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<!-- 1. Hero Section -->
<section class="bg-gradient-to-r from-blue-50/40 via-white to-blue-50/40 py-12 md:py-16 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-block px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-brand-navy text-xs font-bold mb-4 uppercase tracking-wide">
            Nationwide Logistics Infrastructure
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold text-brand-navy leading-tight mb-4">
            Complete <span class="text-brand-red">Courier &amp; Cargo Services</span> in India
        </h1>
        <p class="mt-4 text-base md:text-lg text-gray-600 max-w-3xl mx-auto font-medium leading-relaxed">
            From a 500-gram eCommerce parcel to a 10-ton commercial truckload, OneStall Cargo delivers fast, secure, and cost-effective shipping across 29,000+ pin codes in India and 220+ global destinations.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-full bg-brand-red text-white font-bold text-base hover:bg-brand-redHover transition shadow-md shadow-red-500/20">
                Start Shipping Free
            </a>
            <a href="{{ route('pricing') }}" class="px-8 py-3.5 rounded-full border-2 border-brand-navy text-brand-navy font-bold text-base hover:bg-brand-navy hover:text-white transition">
                Calculate Shipping Rates
            </a>
        </div>
    </div>
</section>

<!-- 2. Services Grid Section -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-navy mb-3">Our Core Logistics Solutions</h2>
            <p class="text-sm md:text-base text-gray-600">Engineered for speed, scale, and operational transparency for Indian businesses.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($services as $service)
            <div class="bg-gray-50/70 p-8 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    @if($service->icon)
                    <div class="w-14 h-14 rounded-xl bg-blue-100 text-brand-blue flex items-center justify-center text-2xl mb-6">
                        <i class="{{ $service->icon }}"></i>
                    </div>
                    @endif
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $service->title }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-6">{{ $service->description }}</p>
                </div>
                <div>
                    @php
                        $targetRoute = match($service->slug) {
                            'b2c-express-courier' => route('solutions.b2c'),
                            'b2b-cargo-freight' => route('solutions.b2b'),
                            'international-courier' => route('solutions.international'),
                            'same-day-hyperlocal' => route('solutions.quick'),
                            'courier-franchise-hub' => route('franchise'),
                            'logistics-api-integration' => route('developers'),
                            default => route('contact'),
                        };
                    @endphp
                    <a href="{{ $targetRoute }}" class="inline-flex items-center text-sm font-bold text-brand-red hover:underline">
                        Explore {{ $service->title }} <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-gray-500">
                <p>Loading services...</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- 3. Why Choose OneStall Section -->
<section class="py-14 bg-gray-50 border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-navy mb-3">Why Indian Businesses Choose OneStall Cargo</h2>
            <p class="text-sm md:text-base text-gray-600">The technology edge that drives higher fulfillment and lower return costs.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
            <div class="bg-white p-6 rounded-xl border border-gray-200">
                <div class="text-3xl text-brand-red mb-3"><i class="fa-solid fa-map-location-dot"></i></div>
                <h4 class="text-lg font-bold text-gray-900 mb-1">29,000+ Pin Codes</h4>
                <p class="text-xs text-gray-600">Unmatched delivery reach covering metros, rural districts, and remote borders.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-200">
                <div class="text-3xl text-brand-red mb-3"><i class="fa-solid fa-money-bill-transfer"></i></div>
                <h4 class="text-lg font-bold text-gray-900 mb-1">1-2 Day COD Remittance</h4>
                <p class="text-xs text-gray-600">Never face cash crunches. Fast automated settlement cycles directly to your bank.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-200">
                <div class="text-3xl text-brand-red mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                <h4 class="text-lg font-bold text-gray-900 mb-1">Video Fraud Protection</h4>
                <p class="text-xs text-gray-600">Timestamped packing video recordings eliminate fake claims and weight disputes.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-200">
                <div class="text-3xl text-brand-red mb-3"><i class="fa-solid fa-headset"></i></div>
                <h4 class="text-lg font-bold text-gray-900 mb-1">Dedicated Account Managers</h4>
                <p class="text-xs text-gray-600">Real human logistics experts standing by for fast escalation resolution.</p>
            </div>
        </div>
    </div>
</section>

<!-- 4. CTA Section -->
<section class="py-12 bg-gradient-to-r from-brand-navy to-brand-blue text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-4xl font-extrabold mb-4">Ready to Transform Your Shipping Operations?</h2>
        <p class="text-base text-gray-300 max-w-2xl mx-auto mb-8">Sign up in 2 minutes. No subscription fees, no credit card required to start comparing courier rates.</p>
        <a href="{{ route('register') }}" class="inline-block px-8 py-3.5 rounded-full bg-brand-red text-white font-bold text-base hover:bg-brand-redHover transition shadow-lg">
            Create Free Account Now
        </a>
    </div>
</section>
@endsection
