@extends('layouts.public')

@php
    $docType = match($title) {
        'Terms of Service' => 'terms',
        'Cookie Policy' => 'cookies',
        default => 'privacy',
    };

    $metaTitles = [
        'privacy' => 'Privacy Policy | OneStall Cargo Logistics India',
        'terms' => 'Terms of Service | OneStall Cargo Logistics Platform',
        'cookies' => 'Cookie Policy | OneStall Cargo Logistics Platform',
    ];

    $metaDescs = [
        'privacy' => 'Official privacy policy of OneStall Cargo (Anyurva Ayurveda Private Limited). Learn how we protect shipper data, shipment tracking information, and personal identity.',
        'terms' => 'Terms and conditions governing courier shipments, commercial freight booking, COD remittance, wallet balance, and user accounts on OneStall Cargo.',
        'cookies' => 'Understand how OneStall Cargo uses cookies and browser storage technologies to maintain secure user sessions, tracking data, and platform analytics.',
    ];
@endphp

@section('title', $metaTitles[$docType])
@section('meta_description', $metaDescs[$docType])
@section('meta_keywords', 'OneStall Cargo ' . $title . ', Logistics Legal Terms, Indian Information Technology Act Compliance')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">{{ $title }}</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    $title => url()->current()
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<section class="bg-gray-50 py-12 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 pb-6 mb-8">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-red">Official Legal Document</span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-brand-navy mt-1">{{ $title }}</h1>
                <p class="text-xs text-gray-400 mt-2">Last Updated: November 2026 &bull; Published by Anyurva Ayurveda Private Limited (CIN: U21003UP2024PTC205462)</p>
            </div>

            <div class="prose max-w-none text-gray-700 leading-relaxed space-y-6 text-sm">
                @if($docType === 'privacy')
                    <h2 class="text-xl font-bold text-gray-900">1. Information We Collect</h2>
                    <p>OneStall Cargo collects information necessary to provide seamless courier aggregation, logistics fulfillment, live tracking, and COD remittance. This includes shipper account details (name, business name, GSTIN, PAN, bank account for remittances, email, phone number) and consignee information (delivery address, pincode, contact phone number).</p>

                    <h2 class="text-xl font-bold text-gray-900">2. How We Use Your Data</h2>
                    <p>We use collected data strictly for booking shipments with carrier partners, generating Air Waybills (AWBs), facilitating automated Non-Delivery Report (NDR) buyer communication, processing COD payouts, and providing real-time tracking updates. We do not sell your personal or customer data to third-party advertisers.</p>

                    <h2 class="text-xl font-bold text-gray-900">3. Data Security & Retention</h2>
                    <p>All sensitive information, including API keys and bank account details, is encrypted using industry-standard AES-256 protocols. Packaging video evidence recorded on our platform is stored securely and accessible only for dispute resolution audits.</p>

                    <h2 class="text-xl font-bold text-gray-900">4. Compliance with Indian Law</h2>
                    <p>This privacy policy is governed by the Information Technology Act, 2000 and the Digital Personal Data Protection (DPDP) Act of India. For privacy concerns, contact our Grievance Officer at <a href="mailto:support@onestallcargo.com" class="text-brand-red font-semibold">support@onestallcargo.com</a>.</p>

                @elseif($docType === 'terms')
                    <h2 class="text-xl font-bold text-gray-900">1. Services Provided</h2>
                    <p>OneStall Cargo operates a logistics technology platform that connects merchants, manufacturers, and individuals with licensed courier and freight carriers. We facilitate rate calculation, label printing, pickup scheduling, tracking, and cash on delivery collection.</p>

                    <h2 class="text-xl font-bold text-gray-900">2. Shipper Obligations & Prohibited Goods</h2>
                    <p>Shippers must accurately declare package contents, dimensions, and actual gross weight. Shippers are strictly prohibited from booking hazardous materials, explosives, illegal contraband, live animals, or currency notes as defined by the Indian Postal Act and carrier regulations.</p>

                    <h2 class="text-xl font-bold text-gray-900">3. Weight Discrepancies & Adjustments</h2>
                    <p>All parcels are re-weighed by carrier automated volumetric scanners. Any discrepancy between declared weight and scanned weight will result in automated adjustment against the shipper's wallet balance. Shippers may submit dispute proof via our Video Evidence platform within 7 calendar days.</p>

                    <h2 class="text-xl font-bold text-gray-900">4. Cash on Delivery (COD) & Remittances</h2>
                    <p>COD funds collected by courier partners are remitted according to the merchant's selected cycle (standard 1-2 days). OneStall Cargo reserves the right to withhold disputed freight charges from COD remittances where fraudulent activity is detected.</p>

                @else
                    <h2 class="text-xl font-bold text-gray-900">1. What Are Cookies?</h2>
                    <p>Cookies are small text files stored in your web browser that enable OneStall Cargo to recognize your device, remember user authentication sessions, preserve dashboard preferences, and analyze website traffic.</p>

                    <h2 class="text-xl font-bold text-gray-900">2. Types of Cookies We Use</h2>
                    <ul class="list-disc pl-5 space-y-2">
                        <li><strong>Essential Cookies:</strong> Required for secure login sessions, CSRF token validation, and wallet balance operations.</li>
                        <li><strong>Performance & Analytics Cookies:</strong> Help us measure page response times, tracking search queries, and optimize Core Web Vitals.</li>
                        <li><strong>Preference Cookies:</strong> Store dashboard filters and user interface settings.</li>
                    </ul>

                    <h2 class="text-xl font-bold text-gray-900">3. Managing Cookie Preferences</h2>
                    <p>You can manage or disable non-essential cookies through your browser settings. However, disabling essential session cookies may prevent you from logging into your seller, hub, or admin dashboard.</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
