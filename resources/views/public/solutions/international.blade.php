@extends('layouts.public')

@section('title', 'International Courier Services from India | Express Air Cargo | OneStall')
@section('meta_description', 'Ship parcels worldwide to 220+ countries with premier international courier services. Automated customs clearance, door-to-door tracking, and competitive air rates.')
@section('meta_keywords', 'International Courier Services, Cross-Border Shipping India, Export Courier Service, Global Air Freight, Customs Clearance Logistics')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-500">Solutions</span>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">International Shipping</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode(seo()->serviceSchema([
    'name' => 'OneStall International Courier & Air Cargo Services',
    'type' => 'International Air Courier & Cross-Border Logistics',
    'areaServed' => 'Worldwide',
    'description' => 'Worldwide express parcel courier and international air freight delivery from India to over 220 countries with paperless customs clearance.'
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Solutions' => route('services'),
    'International Shipping' => route('solutions.international')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<!-- 1. Hero Section -->
<section class="bg-gradient-to-r from-blue-50/40 via-white to-blue-50/40 py-12 md:py-16 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2 text-center lg:text-left">
                <div class="inline-block px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-brand-navy text-xs font-bold mb-4 uppercase tracking-wide">
                    Cross-Border Global Logistics
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-brand-navy leading-tight mb-4">
                    International Courier Services to <span class="text-brand-red">220+ Countries</span>
                </h1>
                <p class="text-base md:text-lg text-gray-700 mb-8 font-medium leading-relaxed">
                    Expand your brand globally with express international air courier services, automated commercial invoices, hassle-free customs clearance, and competitive worldwide air rates.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-full bg-brand-red text-white font-bold text-base hover:bg-brand-redHover transition shadow-md shadow-red-500/20">
                        Start Global Shipping
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-3.5 rounded-full border-2 border-brand-navy text-brand-navy font-bold text-base hover:bg-brand-navy hover:text-white transition">
                        Explore International Rates
                    </a>
                </div>
            </div>

            <div class="w-full lg:w-1/2 relative">
                <div class="relative rounded-2xl overflow-hidden shadow-xl border-4 border-white bg-white">
                    <picture>
                        <source srcset="{{ asset('images/dashboard.webp') }}" type="image/webp">
                        <img src="{{ asset('images/dashboard.jpg') }}" alt="International Courier Services from India - OneStall Cargo" width="600" height="380" class="w-full h-80 md:h-96 object-cover">
                    </picture>
                </div>
                <div class="absolute -bottom-4 -left-4 bg-white p-3.5 rounded-xl shadow-lg border border-gray-100 flex items-center gap-3 z-20">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold">
                        <i class="fa-solid fa-plane-departure"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Global Network</div>
                        <div class="text-sm font-bold text-brand-navy">220+ Destinations</div>
                    </div>
                </div>
                <div class="absolute -top-4 -right-4 bg-white p-3.5 rounded-xl shadow-lg border border-gray-100 flex items-center gap-3 z-20">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-brand-navy font-bold">
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Customs Clearance</div>
                        <div class="text-sm font-bold text-brand-red">Paperless & Automated</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Metrics Bar -->
<section class="bg-brand-navy py-6 text-white border-y border-brand-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-2xl md:text-3xl font-black text-white">220+</div>
                <div class="text-xs md:text-sm text-gray-300">Countries Served</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-black text-white">3-7 Days</div>
                <div class="text-xs md:text-sm text-gray-300">Express Transit Time</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-black text-brand-red">DDP / DDU</div>
                <div class="text-xs md:text-sm text-gray-300">Duty Payment Options</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-black text-white">IOSS / VAT</div>
                <div class="text-xs md:text-sm text-gray-300">Tax Compliance</div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Key Capabilities Grid -->
<section class="py-12 md:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-4xl font-extrabold text-brand-navy mb-3">
                Comprehensive Cross-Border Shipping Features
            </h2>
            <p class="text-base text-gray-600">
                Everything required to export goods globally without getting bogged down by international regulations.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-brand-navy flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-plane"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Express Air Courier</h3>
                <p class="text-sm text-gray-600">Partner with DHL, FedEx, and Aramex for fast 3-7 business day doorstep deliveries worldwide.</p>
            </div>

            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-red-100 text-brand-red flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-passport"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Automated Customs Documentation</h3>
                <p class="text-sm text-gray-600">Generate commercial invoices, CSB-V export declarations, and shipping bills digitally in real-time.</p>
            </div>

            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Delivered Duty Paid (DDP)</h3>
                <p class="text-sm text-gray-600">Pre-calculate and pay import duties upfront so your buyers face zero unexpected fees at final delivery.</p>
            </div>

            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-earth-americas"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">End-to-End Global Tracking</h3>
                <p class="text-sm text-gray-600">Single tracking link from origin pickup through international customs to final doorstep handover.</p>
            </div>

            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">IOSS & UK VAT Ready</h3>
                <p class="text-sm text-gray-600">Comply smoothly with EU IOSS and UK VAT regulations for direct-to-consumer e-commerce sales.</p>
            </div>

            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-2xl mb-4">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Multi-Currency Billing</h3>
                <p class="text-sm text-gray-600">Clear rate breakdowns in ₹, ₹, EUR, and GBP with no hidden fuel surcharge surprises.</p>
            </div>
        </div>
    </div>
</section>

<!-- 4. Workflow Section -->
<section class="py-12 md:py-16 bg-gray-50/50 border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-brand-navy mb-2">
                4 Steps to Ship Internationally
            </h2>
            <p class="text-sm md:text-base text-gray-600 font-medium">Simple cross-border export workflow</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
            <div class="bg-white p-6 rounded-xl border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-brand-navy text-white font-bold flex items-center justify-center mx-auto mb-4 text-sm">1</div>
                <h4 class="font-bold text-gray-900 mb-2 text-base">Assign HS Codes</h4>
                <p class="text-xs text-gray-600">Select product categories & auto-populate international HS codes.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-brand-navy text-white font-bold flex items-center justify-center mx-auto mb-4 text-sm">2</div>
                <h4 class="font-bold text-gray-900 mb-2 text-base">Auto-Invoicing</h4>
                <p class="text-xs text-gray-600">Commercial invoices and customs labels generated automatically.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-brand-navy text-white font-bold flex items-center justify-center mx-auto mb-4 text-sm">3</div>
                <h4 class="font-bold text-gray-900 mb-2 text-base">Air Cargo Export</h4>
                <p class="text-xs text-gray-600">Fast clearance at Indian international air cargo hubs.</p>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-brand-navy text-white font-bold flex items-center justify-center mx-auto mb-4 text-sm">4</div>
                <h4 class="font-bold text-gray-900 mb-2 text-base">Global Doorstep Delivery</h4>
                <p class="text-xs text-gray-600">Final delivery via top destination country courier partners.</p>
            </div>
        </div>
    </div>
</section>

<!-- 5. CTA Card -->
<section class="py-12 md:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-brand-navy via-brand-blue to-brand-navy rounded-3xl p-8 md:p-12 text-white text-center shadow-xl">
            <h2 class="text-2xl md:text-4xl font-extrabold mb-4">
                Ready to Sell Your Products Globally?
            </h2>
            <p class="text-sm md:text-base text-gray-200 max-w-2xl mx-auto mb-8 font-medium">
                Start shipping to 220+ countries with automated customs support and competitive international air freight rates.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-full bg-brand-red text-white font-bold text-base hover:bg-brand-redHover transition shadow-md">
                    Start International Shipping
                </a>
                <a href="{{ route('contact') }}" class="px-8 py-3.5 rounded-full border border-white/40 text-white font-bold text-base hover:bg-white/10 transition">
                    Contact Export Desk
                </a>
            </div>
        </div>
    </div>
</section>
@endsection