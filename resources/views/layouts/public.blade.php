<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $seoService = app(\App\Services\SeoService::class);
        $currentMeta = $seoService->getCurrentMeta();
        $rawTitle = html_entity_decode(trim($__env->yieldContent('title', $currentMeta['title'])), ENT_QUOTES, 'UTF-8');
        $rawDesc = html_entity_decode(trim($__env->yieldContent('meta_description', $currentMeta['description'])), ENT_QUOTES, 'UTF-8');
        $rawKeywords = html_entity_decode(trim($__env->yieldContent('meta_keywords', $currentMeta['keywords'])), ENT_QUOTES, 'UTF-8');
        $canonicalUrl = trim($__env->yieldContent('canonical_url', $seoService->getCanonicalUrl()));
        $robotsDirective = trim($__env->yieldContent('meta_robots', $seoService->getRobotsDirective()));
        $ogType = trim($__env->yieldContent('og_type', $currentMeta['type'] ?? 'website'));
        $ogImage = trim($__env->yieldContent('og_image', asset(file_exists(public_path('images/og-preview.webp')) ? 'images/og-preview.webp' : 'images/og-preview.jpg')));
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Primary SEO Metadata -->
    <title>{{ $rawTitle }}</title>
    <meta name="description" content="{{ $rawDesc }}">
    <meta name="keywords" content="{{ $rawKeywords }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="{{ $robotsDirective }}">
    <meta name="author" content="OneStall Cargo">
    <meta name="publisher" content="ANYURVA AYURVEDA PRIVATE LIMITED">

    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:locale" content="en_IN">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="OneStall Cargo">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $rawTitle }}">
    <meta property="og:description" content="{{ $rawDesc }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="OneStall Cargo - Tech-Enabled Logistics & Courier Platform">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@onestallcargo">
    <meta name="twitter:creator" content="@onestallcargo">
    <meta name="twitter:url" content="{{ $canonicalUrl }}">
    <meta name="twitter:title" content="{{ $rawTitle }}">
    <meta name="twitter:description" content="{{ $rawDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Search Console & Verification Tags Hook -->
    @if(setting('google_site_verification'))
    <meta name="google-site-verification" content="{{ setting('google_site_verification') }}">
    @endif
    @yield('meta_tags')

    <!-- Preconnect & DNS-Prefetch for Core Web Vitals -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (via CDN with custom config) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            navy: '#091024',
                            blue: '#111D3D',
                            red: '#E7004C',
                            redHover: '#C70041',
                        }
                    }
                }
            }
        }
    </script>
    <!-- AlpineJS for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(135deg, #001960 0%, #021A62 100%); }
    </style>

    <!-- Global Structured Data: Organization & WebSite Schemas -->
    <script type="application/ld+json">
    {!! json_encode($seoService->organizationSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @if(Request::is('/') || Request::routeIs('home'))
    <script type="application/ld+json">
    {!! json_encode($seoService->websiteSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @endif

    <!-- Page-Specific Structured Data Hook -->
    @yield('schema_json')
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

    <!-- Global Navigation -->
    <header class="bg-white text-brand-navy sticky top-0 z-50 shadow-sm border-b border-gray-100" x-data="{ mobileMenu: false, activeDropdown: null }">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Left: Logo & Primary Navigation -->
                <div class="flex items-center space-x-8 xl:space-x-12">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="flex items-center group transition-transform hover:opacity-95" aria-label="OneStall Cargo Homepage">
                            <picture>
                                @if(file_exists(public_path('images/logo.webp')))
                                <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
                                @endif
                                <img src="{{ asset('images/logo.jpg') }}" alt="OneStall Cargo - Leading Logistics & Courier Company" width="160" height="48" class="h-10 sm:h-11 md:h-12 w-auto object-contain">
                            </picture>
                        </a>
                    </div>

                    <!-- Desktop Navigation Menu -->
                    <nav class="hidden lg:flex items-center space-x-5 xl:space-x-7" aria-label="Primary Navigation">
                        
                        <!-- Solutions Dropdown -->
                        <div class="relative group" @mouseenter="activeDropdown = 'solutions'" @mouseleave="activeDropdown = null">
                            <button class="text-xs xl:text-sm font-semibold text-gray-700 hover:text-brand-red flex items-center h-20 py-0 whitespace-nowrap transition-colors focus:outline-none">
                                Solutions <i class="fa-solid fa-chevron-down ml-1.5 text-[10px] text-gray-400 group-hover:text-brand-red transition-transform" :class="activeDropdown === 'solutions' ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="activeDropdown === 'solutions'" x-transition x-cloak class="absolute left-0 top-full -mt-1 w-64 bg-white text-gray-800 rounded-b-xl shadow-xl border-t-2 border-brand-red overflow-hidden z-50">
                                <a href="{{ route('solutions.b2c') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-box text-brand-blue w-5"></i> B2C Express Shipping</a>
                                <a href="{{ route('solutions.b2b') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-truck-moving text-brand-blue w-5"></i> B2B Cargo & Bulk Freight</a>
                                <a href="{{ route('solutions.international') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-plane text-brand-blue w-5"></i> International Shipping</a>
                                <a href="{{ route('solutions.quick') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-bolt text-brand-blue w-5"></i> Quick Same-Day Delivery</a>
                                <a href="{{ route('solutions.ecommerce') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-store text-brand-blue w-5"></i> E-commerce Integration</a>
                                <a href="{{ route('services') }}" class="block px-5 py-3 text-sm font-bold text-brand-red hover:bg-gray-50"><i class="fa-solid fa-list-check w-5"></i> All Logistics Services &rarr;</a>
                            </div>
                        </div>

                        <!-- Platform Dropdown -->
                        <div class="relative group" @mouseenter="activeDropdown = 'platform'" @mouseleave="activeDropdown = null">
                            <button class="text-xs xl:text-sm font-semibold text-gray-700 hover:text-brand-red flex items-center h-20 py-0 whitespace-nowrap transition-colors focus:outline-none">
                                Platform <i class="fa-solid fa-chevron-down ml-1.5 text-[10px] text-gray-400 group-hover:text-brand-red transition-transform" :class="activeDropdown === 'platform' ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="activeDropdown === 'platform'" x-transition x-cloak class="absolute left-0 top-full -mt-1 w-64 bg-white text-gray-800 rounded-b-xl shadow-xl border-t-2 border-brand-red overflow-hidden z-50">
                                <a href="{{ route('platform.tracking') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-location-crosshairs text-brand-blue w-5"></i> Shipment Tracking</a>
                                <a href="{{ route('platform.evidence') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-video text-brand-blue w-5"></i> Video Evidence</a>
                                <a href="{{ route('platform.ndr') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-phone-volume text-brand-blue w-5"></i> NDR Management</a>
                                <a href="{{ route('platform.rto') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-arrow-rotate-left text-brand-blue w-5"></i> RTO Management</a>
                                <a href="{{ route('platform.cod') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-indian-rupee-sign text-brand-blue w-5"></i> COD & Settlement</a>
                                <a href="{{ route('platform.awb') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50"><i class="fa-solid fa-barcode text-brand-blue w-5"></i> AWB & Labels</a>
                            </div>
                        </div>

                        <!-- Developers Dropdown -->
                        <div class="relative group" @mouseenter="activeDropdown = 'developers'" @mouseleave="activeDropdown = null">
                            <button class="text-xs xl:text-sm font-semibold text-gray-700 hover:text-brand-red flex items-center h-20 py-0 whitespace-nowrap transition-colors focus:outline-none">
                                Developers <i class="fa-solid fa-chevron-down ml-1.5 text-[10px] text-gray-400 group-hover:text-brand-red transition-transform" :class="activeDropdown === 'developers' ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="activeDropdown === 'developers'" x-transition x-cloak class="absolute left-0 top-full -mt-1 w-56 bg-white text-gray-800 rounded-b-xl shadow-xl border-t-2 border-brand-red overflow-hidden z-50">
                                <a href="{{ route('developers') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-code text-brand-blue w-5"></i> API Platform</a>
                                <a href="{{ route('docs') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-book text-brand-blue w-5"></i> API Documentation</a>
                                <a href="{{ route('docs.postman') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50"><i class="fa-solid fa-cloud-arrow-down text-brand-blue w-5"></i> Postman Collection</a>
                            </div>
                        </div>

                        <!-- Business Dropdown -->
                        <div class="relative group" @mouseenter="activeDropdown = 'business'" @mouseleave="activeDropdown = null">
                            <button class="text-xs xl:text-sm font-semibold text-gray-700 hover:text-brand-red flex items-center h-20 py-0 whitespace-nowrap transition-colors focus:outline-none">
                                Business <i class="fa-solid fa-chevron-down ml-1.5 text-[10px] text-gray-400 group-hover:text-brand-red transition-transform" :class="activeDropdown === 'business' ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="activeDropdown === 'business'" x-transition x-cloak class="absolute left-0 top-full -mt-1 w-52 bg-white text-gray-800 rounded-b-xl shadow-xl border-t-2 border-brand-red overflow-hidden z-50">
                                <a href="{{ route('pricing') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-tags text-brand-blue w-5"></i> Rate Calculator</a>
                                <a href="{{ route('franchise') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-store text-brand-blue w-5"></i> Courier Franchise</a>
                                <a href="{{ route('corporate') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-building text-brand-blue w-5"></i> Corporate Supply Chain</a>
                                <a href="{{ route('partners') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50"><i class="fa-solid fa-handshake text-brand-blue w-5"></i> Fleet Partners</a>
                            </div>
                        </div>

                        <!-- Resources Dropdown -->
                        <div class="relative group" @mouseenter="activeDropdown = 'resources'" @mouseleave="activeDropdown = null">
                            <button class="text-xs xl:text-sm font-semibold text-gray-700 hover:text-brand-red flex items-center h-20 py-0 whitespace-nowrap transition-colors focus:outline-none">
                                Resources <i class="fa-solid fa-chevron-down ml-1.5 text-[10px] text-gray-400 group-hover:text-brand-red transition-transform" :class="activeDropdown === 'resources' ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="activeDropdown === 'resources'" x-transition x-cloak class="absolute left-0 top-full -mt-1 w-52 bg-white text-gray-800 rounded-b-xl shadow-xl border-t-2 border-brand-red overflow-hidden z-50">
                                <a href="{{ url('/blog') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-newspaper text-brand-blue w-5"></i> Logistics Blog</a>
                                <a href="{{ url('/serviceability') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-map-location-dot text-brand-blue w-5"></i> Pincode Checker</a>
                                <a href="{{ route('about') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-circle-info text-brand-blue w-5"></i> About Us</a>
                                <a href="{{ route('faq') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-circle-question text-brand-blue w-5"></i> FAQ</a>
                                <a href="{{ route('help') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50 border-b border-gray-100"><i class="fa-solid fa-life-ring text-brand-blue w-5"></i> Help Center</a>
                                <a href="{{ route('contact') }}" class="block px-5 py-3 text-sm font-medium hover:bg-gray-50"><i class="fa-solid fa-address-book text-brand-blue w-5"></i> Contact Us</a>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Right: Desktop CTAs -->
                <div class="hidden lg:flex items-center space-x-2.5 xl:space-x-3 flex-shrink-0">
                    <a href="tel:+917376490981" class="hidden xl:inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-brand-red transition px-3.5 py-2 rounded-full bg-gray-100 hover:bg-gray-200/80 whitespace-nowrap flex-shrink-0" title="Toll-Free Helpline">
                        <i class="fa-solid fa-phone text-brand-red text-[11px]"></i> +91 73764 90981
                    </a>
                    <a href="{{ route('track') }}" class="inline-flex items-center gap-1.5 text-xs xl:text-sm font-semibold text-gray-700 hover:text-brand-red transition px-2.5 xl:px-3 py-2 rounded-lg hover:bg-gray-50 whitespace-nowrap flex-shrink-0">
                        <i class="fa-solid fa-location-crosshairs text-brand-red"></i> Track Shipment
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center text-xs xl:text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-full px-4 xl:px-5 py-2 hover:bg-gray-50 hover:border-gray-400 transition whitespace-nowrap flex-shrink-0">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 xl:px-5 py-2 rounded-full text-xs xl:text-sm font-bold bg-brand-red hover:bg-brand-redHover text-white transition shadow-sm whitespace-nowrap flex-shrink-0">Sign Up Free</a>
                </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden flex items-center">
                    <button @click="mobileMenu = !mobileMenu" class="text-gray-700 hover:text-brand-red focus:outline-none p-2" aria-label="Toggle Navigation Menu">
                        <i class="fa-solid fa-bars text-2xl" x-show="!mobileMenu"></i>
                        <i class="fa-solid fa-xmark text-2xl" x-show="mobileMenu" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden flex items-center">
                    <button @click="mobileMenu = !mobileMenu" class="text-gray-700 hover:text-brand-red focus:outline-none p-2" aria-label="Toggle Navigation Menu">
                        <i class="fa-solid fa-bars text-2xl" x-show="!mobileMenu"></i>
                        <i class="fa-solid fa-xmark text-2xl" x-show="mobileMenu" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenu" x-transition x-cloak class="lg:hidden bg-brand-blue border-t border-gray-700 h-screen overflow-y-auto">
            <div class="px-4 pt-4 pb-32 space-y-1">
                <a href="{{ route('services') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">All Services</a>
                <a href="{{ route('solutions.b2c') }}" class="block px-3 py-2.5 text-sm text-gray-300 hover:text-white">B2C Express Shipping</a>
                <a href="{{ route('solutions.b2b') }}" class="block px-3 py-2.5 text-sm text-gray-300 hover:text-white">B2B Cargo & Bulk Freight</a>
                <a href="{{ route('solutions.international') }}" class="block px-3 py-2.5 text-sm text-gray-300 hover:text-white">International Shipping</a>
                <a href="{{ route('solutions.quick') }}" class="block px-3 py-2.5 text-sm text-gray-300 hover:text-white">Quick Hyperlocal Delivery</a>
                <a href="{{ route('solutions.ecommerce') }}" class="block px-3 py-2.5 text-sm text-gray-300 hover:text-white">E-commerce Integration</a>
                <div class="border-t border-gray-700 my-2"></div>
                <a href="{{ route('pricing') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">Rate Calculator</a>
                <a href="{{ route('franchise') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">Courier Franchise</a>
                <a href="{{ url('/blog') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">Logistics Blog</a>
                <a href="{{ url('/serviceability') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">Pincode Serviceability</a>
                <a href="{{ route('developers') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">Developers API</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2.5 text-base font-semibold text-white hover:bg-gray-800 rounded">Contact Us</a>
                <div class="border-t border-gray-700 my-2"></div>
                <a href="{{ route('track') }}" class="block px-3 py-3 text-base font-bold text-brand-red"><i class="fa-solid fa-location-crosshairs mr-1"></i> Track Shipment</a>
                <a href="{{ route('login') }}" class="block px-3 py-2.5 text-base font-semibold text-white">Login</a>
                <a href="{{ route('register') }}" class="block px-3 py-3 text-base font-bold text-white bg-brand-red rounded mt-2 text-center">Get Started Free</a>
            </div>
        </div>
    </header>

    <!-- Optional Breadcrumbs Section -->
    @hasSection('breadcrumbs')
    <nav class="bg-gray-100/80 border-b border-gray-200 py-2.5 text-xs text-gray-600" aria-label="Breadcrumb">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center space-x-2">
            <a href="/" class="hover:text-brand-red transition"><i class="fa-solid fa-house text-[11px] mr-1"></i> Home</a>
            @yield('breadcrumbs')
        </div>
    </nav>
    @endif

    <!-- Main Content -->
    <main class="flex-grow w-full overflow-hidden" id="main-content">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-8">
                <!-- Column 1: Company Profile -->
                <div class="lg:col-span-2">
                    <div class="mb-5 bg-white inline-block p-1.5 rounded-lg">
                        <picture>
                            @if(file_exists(public_path('images/logo.webp')))
                            <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
                            @endif
                            <img src="{{ asset('images/logo.jpg') }}" alt="OneStall Cargo" width="160" height="48" class="h-11 w-auto">
                        </picture>
                    </div>
                    <p class="text-sm leading-relaxed mb-4 text-gray-400">
                        OneStall Cargo is India's tech-enabled logistics platform powering 10,000+ businesses with automated courier allocation, 1-2 day COD remittance, heavy B2B freight, and a nationwide network covering 29,000+ pin codes.
                    </p>

                    <!-- Official Social Media Channels -->
                    <div class="mt-4 mb-3">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2.5">Official Social Channels</span>
                        <div class="flex items-center space-x-2.5">
                            <a href="{{ setting('youtube_url', 'https://www.youtube.com/@onestallcargoindia') }}" target="_blank" rel="noopener noreferrer" aria-label="OneStall Cargo on YouTube" class="w-8 h-8 rounded-lg bg-gray-800 text-gray-300 hover:bg-[#FF0000] hover:text-white flex items-center justify-center transition" title="YouTube">
                                <i class="fa-brands fa-youtube text-sm"></i>
                            </a>
                            <a href="{{ setting('instagram_url', 'https://www.instagram.com/onestallcargo') }}" target="_blank" rel="noopener noreferrer" aria-label="OneStall Cargo on Instagram" class="w-8 h-8 rounded-lg bg-gray-800 text-gray-300 hover:bg-[#E4405F] hover:text-white flex items-center justify-center transition" title="Instagram">
                                <i class="fa-brands fa-instagram text-sm"></i>
                            </a>
                            <a href="{{ setting('linkedin_url', 'https://www.linkedin.com/company/onestallcargo') }}" target="_blank" rel="noopener noreferrer" aria-label="OneStall Cargo on LinkedIn" class="w-8 h-8 rounded-lg bg-gray-800 text-gray-300 hover:bg-[#0077b5] hover:text-white flex items-center justify-center transition" title="LinkedIn">
                                <i class="fa-brands fa-linkedin-in text-sm"></i>
                            </a>
                            <a href="{{ setting('twitter_url', 'https://twitter.com/onestallcargo') }}" target="_blank" rel="noopener noreferrer" aria-label="OneStall Cargo on X (Twitter)" class="w-8 h-8 rounded-lg bg-gray-800 text-gray-300 hover:bg-black hover:text-white flex items-center justify-center transition" title="X (Twitter)">
                                <i class="fa-brands fa-x-twitter text-sm"></i>
                            </a>
                            <a href="{{ setting('facebook_url', 'https://www.facebook.com/onestallcargo') }}" target="_blank" rel="noopener noreferrer" aria-label="OneStall Cargo on Facebook" class="w-8 h-8 rounded-lg bg-gray-800 text-gray-300 hover:bg-[#1877F2] hover:text-white flex items-center justify-center transition" title="Facebook">
                                <i class="fa-brands fa-facebook-f text-sm"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Registered Corporate Office Info -->
                    <div class="mt-4 space-y-1.5 text-xs text-gray-400 leading-relaxed border-t border-gray-800 pt-4">
                        <p class="font-bold text-gray-300 uppercase tracking-wider text-[11px]">Registered Corporate Entity</p>
                        <p class="font-semibold text-white">ANYURVA AYURVEDA PRIVATE LIMITED</p>
                        <p>CIN: <span class="font-mono text-gray-300">U21003UP2024PTC205462</span></p>
                        <p>569/153 KHA, Barigawan, KP Road, 32 BN PAC,<br>Lucknow – 226023, Uttar Pradesh, India</p>
                        <p class="pt-1"><i class="fa-solid fa-phone text-brand-red mr-1.5"></i> Toll-Free / Helpline: <a href="tel:+917376490981" class="text-white hover:underline font-semibold">+91 73764 90981</a></p>
                        <p><i class="fa-solid fa-envelope text-brand-red mr-1.5"></i> support@onestallcargo.com</p>
                    </div>
                </div>

                <!-- Column 2: Solutions -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-xs uppercase tracking-wider">Solutions</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('solutions.b2c') }}" class="hover:text-white transition">B2C Express Shipping</a></li>
                        <li><a href="{{ route('solutions.b2b') }}" class="hover:text-white transition">B2B Cargo & Freight</a></li>
                        <li><a href="{{ route('solutions.international') }}" class="hover:text-white transition">International Shipping</a></li>
                        <li><a href="{{ route('solutions.quick') }}" class="hover:text-white transition">Hyperlocal Quick Delivery</a></li>
                        <li><a href="{{ route('solutions.ecommerce') }}" class="hover:text-white transition">E-commerce Integration</a></li>
                        <li><a href="{{ route('services') }}" class="text-brand-red font-semibold hover:underline">All Services &rarr;</a></li>
                    </ul>
                </div>

                <!-- Column 3: Platform -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-xs uppercase tracking-wider">Platform</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('platform.tracking') }}" class="hover:text-white transition">Shipment Tracking</a></li>
                        <li><a href="{{ route('platform.evidence') }}" class="hover:text-white transition">Video Evidence</a></li>
                        <li><a href="{{ route('platform.ndr') }}" class="hover:text-white transition">NDR Management</a></li>
                        <li><a href="{{ route('platform.rto') }}" class="hover:text-white transition">RTO Management</a></li>
                        <li><a href="{{ route('platform.cod') }}" class="hover:text-white transition">COD Remittance</a></li>
                        <li><a href="{{ route('platform.awb') }}" class="hover:text-white transition">AWB & Shipping Labels</a></li>
                    </ul>
                </div>

                <!-- Column 4: Developers & Network -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-xs uppercase tracking-wider">Developers & Tools</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('developers') }}" class="hover:text-white transition">API Overview</a></li>
                        <li><a href="{{ route('docs') }}" class="hover:text-white transition">API Documentation</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition">Rate Calculator</a></li>
                        <li><a href="{{ url('/serviceability') }}" class="hover:text-white transition">Pincode Serviceability</a></li>
                        <li><a href="{{ route('track') }}" class="hover:text-white transition">Live AWB Tracking</a></li>
                    </ul>
                </div>

                <!-- Column 5: Company & Locations -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-xs uppercase tracking-wider">Company & Hubs</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:text-white transition">About Us</a></li>
                        <li><a href="{{ url('/blog') }}" class="hover:text-white transition">Logistics Blog</a></li>
                        <li><a href="{{ route('franchise') }}" class="hover:text-white transition">Courier Franchise</a></li>
                        <li><a href="{{ route('corporate') }}" class="hover:text-white transition">Corporate Supply Chain</a></li>
                        <li><a href="{{ route('partners') }}" class="hover:text-white transition">Fleet Partners</a></li>
                        <li><a href="{{ url('/locations/delhi-ncr') }}" class="hover:text-white transition">Delhi NCR Central Hub</a></li>
                        <li><a href="{{ url('/locations/lucknow-up') }}" class="hover:text-white transition">Lucknow UP Distribution Hub</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-white transition">FAQs</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Disclaimer & Legal Links -->
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-xs gap-4">
                <div class="text-center md:text-left text-gray-500">
                    <p>&copy; {{ date('Y') }} OneStall Cargo &mdash; A Brand of <span class="text-gray-300 font-semibold">ANYURVA AYURVEDA PRIVATE LIMITED</span></p>
                    <p class="mt-1">CIN: U21003UP2024PTC205462 &nbsp;|&nbsp; Registered Office: Lucknow, Uttar Pradesh &nbsp;|&nbsp; All Rights Reserved.</p>
                </div>
                <div class="flex flex-wrap justify-center space-x-6 text-gray-400">
                    <a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="hover:text-white transition">Terms of Service</a>
                    <a href="{{ route('cookies') }}" class="hover:text-white transition">Cookie Policy</a>
                    <a href="{{ route('sitemap') }}" class="hover:text-white transition">XML Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
