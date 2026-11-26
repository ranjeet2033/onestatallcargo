<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;

class SeoService
{
    protected string $siteName = 'OneStall Cargo';
    protected string $corporateName = 'ANYURVA AYURVEDA PRIVATE LIMITED';
    protected string $cin = 'U21003UP2024PTC205462';
    protected string $baseUrl = 'https://onestallcargo.com';
    protected string $defaultImage = 'images/og-preview.jpg';
    protected string $logoUrl = 'images/logo.jpg';

    /**
     * Master route-to-metadata registry for all public routes.
     */
    protected array $routeMeta = [
        'home' => [
            'title' => 'Courier Service in India | OneStall Cargo Logistics',
            'description' => 'Reliable courier service in India for eCommerce and B2B brands. Deliver to 29,000+ pin codes with 15+ top couriers, automated NDR, and 1-2 day COD remittance.',
            'keywords' => 'Courier Service in India, Shipping Company in India, E-commerce Shipping Solutions, Courier and Logistics Platform, Express Courier India',
            'type' => 'website',
            'priority' => 1.0,
            'changefreq' => 'daily',
        ],
        'services' => [
            'title' => 'Shipping Company in India | Express Courier & Cargo Services',
            'description' => 'Discover all services from OneStall Cargo, India\'s trusted shipping company. B2C express courier, B2B freight, international cargo, and franchise hub logistics.',
            'keywords' => 'Shipping Company in India, Express Courier Services, B2B Cargo Services, Domestic Freight Transport, Warehousing Logistics',
            'type' => 'website',
            'priority' => 0.9,
            'changefreq' => 'weekly',
        ],
        'solutions.b2c' => [
            'title' => 'B2C Courier Service in India | Fast E-Commerce Shipping | OneStall',
            'description' => 'Scale your online store with India\'s premier B2C courier service. Reach 29,000+ pin codes, reduce RTO by up to 40%, and enjoy 1-2 day express COD remittance.',
            'keywords' => 'Courier Service in India, B2C Express Courier, eCommerce Parcel Delivery, Doorstep Courier Delivery, Low RTO Shipping',
            'type' => 'service',
            'priority' => 0.9,
            'changefreq' => 'weekly',
        ],
        'solutions.b2b' => [
            'title' => 'B2B Cargo Services & Bulk Freight Transport | OneStall Cargo',
            'description' => 'Streamline heavy freight with trusted B2B cargo services in India. Reliable LTL and FTL logistics, discounted commercial freight rates, and 100% transit insurance.',
            'keywords' => 'B2B Cargo Services, Bulk Shipping Services, Heavy Freight Delivery India, LTL and FTL Logistics, Commercial Freight Transport',
            'type' => 'service',
            'priority' => 0.9,
            'changefreq' => 'weekly',
        ],
        'solutions.international' => [
            'title' => 'International Courier Services from India | Express Air Cargo | OneStall',
            'description' => 'Ship parcels worldwide to 220+ countries with premier international courier services. Automated customs clearance, door-to-door tracking, and competitive air rates.',
            'keywords' => 'International Courier Services, Cross-Border Shipping India, Export Courier Service, Global Air Freight, Customs Clearance Logistics',
            'type' => 'service',
            'priority' => 0.9,
            'changefreq' => 'weekly',
        ],
        'solutions.quick' => [
            'title' => 'Same Day Courier & Hyperlocal Delivery | OneStall Cargo',
            'description' => 'Fast same-day courier service and hyperlocal 2-hour bike deliveries in major cities. Live GPS tracking, instant rider dispatch, and on-demand city parcel delivery.',
            'keywords' => 'Bulk Shipping Services, Same Day Delivery India, Hyperlocal Courier Service, 2-Hour Delivery, Local Bike Logistics',
            'type' => 'service',
            'priority' => 0.8,
            'changefreq' => 'weekly',
        ],
        'solutions.ecommerce' => [
            'title' => 'E-Commerce Shipping Solutions & Logistics Platform India | OneStall',
            'description' => 'All-in-one e-commerce shipping solutions in India. Integrate Shopify, WooCommerce, and marketplaces to automate AWB generation, courier allocation, and NDR tracking.',
            'keywords' => 'E-commerce Shipping Solutions, Multi-Carrier Logistics Platform, Courier Shipping India, Shopify Courier Integration, WooCommerce Shipping',
            'type' => 'service',
            'priority' => 0.9,
            'changefreq' => 'weekly',
        ],
        'franchise' => [
            'title' => 'Courier Franchise in India | High ROI Logistics Partner | OneStall',
            'description' => 'Start a profitable courier franchise in India with OneStall Cargo. Own exclusive district delivery rights, earn 35-45% profit margins, and access complete tech support.',
            'keywords' => 'Courier Franchise in India, Logistics Hub Franchise, Courier Booking Counter Franchise, High ROI Franchise Opportunity, District Hub Partner',
            'type' => 'business.business',
            'priority' => 0.9,
            'changefreq' => 'weekly',
        ],
        'corporate' => [
            'title' => 'Business Logistics Solutions & Enterprise Cargo | OneStall Cargo',
            'description' => 'Scalable business logistics solutions for corporate enterprises. Custom SLA contracts, dedicated freight trucks, ERP integration, and 15-30 day credit billing.',
            'keywords' => 'Business Logistics Solutions, Enterprise Supply Chain, Corporate Courier Contracts, Credit Billing Shipping, Dedicated Freight Fleet',
            'type' => 'service',
            'priority' => 0.8,
            'changefreq' => 'monthly',
        ],
        'developers' => [
            'title' => 'Logistics API Integration & Shipping APIs | OneStall Cargo',
            'description' => 'Developer-first logistics API integration for e-commerce platforms. Instant rate comparison, AWB generation, live tracking webhooks, and sandbox testing keys.',
            'keywords' => 'Logistics API Integration, Shipping API India, Courier Booking API, Tracking Webhook API, Automated AWB Generation API',
            'type' => 'website',
            'priority' => 0.8,
            'changefreq' => 'monthly',
        ],
        'docs' => [
            'title' => 'Shipping & Courier REST API Documentation | OneStall Cargo',
            'description' => 'Comprehensive developer documentation for OneStall Cargo RESTful APIs. Endpoints for rate calculation, order booking, manifest creation, and webhook event payloads.',
            'keywords' => 'Logistics API Documentation, REST API Logistics, Courier Webhooks Guide, Shipping Integration Docs, Postman Collection',
            'type' => 'article',
            'priority' => 0.8,
            'changefreq' => 'monthly',
        ],
        'pricing' => [
            'title' => 'Shipping Cost Calculator | Compare Courier Rates India | OneStall',
            'description' => 'Calculate shipping rates in seconds with India\'s premier tech-enabled courier and logistics platform. Compare courier charges across top 15+ delivery partners with zero hidden fees.',
            'keywords' => 'Logistics Platform India, Courier Rate Calculator, Compare Courier Rates, Shipping Cost Calculator India, Cheap Courier Charges',
            'type' => 'website',
            'priority' => 0.8,
            'changefreq' => 'weekly',
        ],
        'track' => [
            'title' => 'Track Shipment Online | Live Courier Tracking India | OneStall Cargo',
            'description' => 'Track your courier shipment in real time across India. Enter your AWB number to get instant live GPS status, delivery milestones, and expected arrival date.',
            'keywords' => 'Live Courier Tracking India, Track Consignment AWB, Real Time Parcel Tracking, Track Order Online, Delivery Status Check',
            'type' => 'website',
            'priority' => 0.8,
            'changefreq' => 'always',
        ],
        'partners' => [
            'title' => 'Logistics & Transport Partner Network | OneStall Cargo',
            'description' => 'Join India\'s fastest-growing logistics partner network. Connect your fleet to receive daily parcel loads, dedicated routes, and guaranteed automated weekly payouts.',
            'keywords' => 'Logistics Partner Network, Transport Fleet Partnership, Courier Carrier Network, Regional Fleet Partner India',
            'type' => 'website',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'about' => [
            'title' => 'About OneStall Cargo | Tech-Driven Logistics Infrastructure',
            'description' => 'Learn about OneStall Cargo (Anyurva Ayurveda Pvt. Ltd.), powering 10,000+ businesses with automated multi-courier shipping, transparent pricing, and nationwide hubs.',
            'keywords' => 'Best Shipping Company in India, Tech-Enabled Logistics Company, Supply Chain Infrastructure India, Anyurva Ayurveda Pvt Ltd',
            'type' => 'website',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'faq' => [
            'title' => 'Courier & Shipping FAQs | OneStall Cargo Help Center',
            'description' => 'Answers to frequently asked questions about OneStall Cargo shipping rates, COD remittance cycles, courier partner integrations, NDR resolution, and franchise setup.',
            'keywords' => 'Courier Service FAQs India, Shipping Queries, COD Remittance Process, NDR Management FAQs, E-commerce Logistics Help',
            'type' => 'website',
            'priority' => 0.7,
            'changefreq' => 'weekly',
        ],
        'help' => [
            'title' => 'Help Center & Shipper Support | OneStall Cargo',
            'description' => 'Access shipper onboarding guides, troubleshooting articles, and 24/7 dedicated logistics support for seamless order fulfillment and delivery operations.',
            'keywords' => 'Seller Logistics Support, Onboarding Shipping Guide, Courier Dispute Support, Weight Dispute Help, Logistics Assistance',
            'type' => 'website',
            'priority' => 0.6,
            'changefreq' => 'monthly',
        ],
        'contact' => [
            'title' => 'Contact OneStall Cargo | Courier & Logistics Support India',
            'description' => 'Contact OneStall Cargo for business inquiries, franchise opportunities, enterprise cargo rates, or support. Registered office in Lucknow, UP & national operational hubs.',
            'keywords' => 'Logistics Company Office India, Courier Customer Care, Business Inquiries Logistics, Enterprise Cargo Contact, OneStall Lucknow Office',
            'type' => 'website',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'platform.video-evidence' => [
            'title' => 'Video Evidence & Fraud Protection | OneStall Cargo Platform',
            'description' => 'Eliminate fake customer claims and courier weight disputes with timestamped packaging video evidence and automated hub handover recordings.',
            'keywords' => 'Anti-Fraud Logistics Platform, Packaging Video Evidence, Courier Damage Protection, Weight Dispute Proof, Fraud Prevention Shipping',
            'type' => 'service',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'platform.ndr' => [
            'title' => 'Automated NDR Management | Lower E-Commerce RTO by 40% | OneStall',
            'description' => 'Reduce courier RTO losses with automated Non-Delivery Report (NDR) workflows, WhatsApp/IVR buyer confirmations, and fake delivery remark detection.',
            'keywords' => 'Automated NDR Management, RTO Reduction System, Undelivered Courier Management, Customer Reattempt Logistics, IVR NDR Verification',
            'type' => 'service',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'platform.rto' => [
            'title' => 'RTO Management & Reverse Logistics | OneStall Cargo Platform',
            'description' => 'Manage return-to-origin shipments with complete tracking, automated reverse pick-ups, QC inspection verification, and instant inventory restocking.',
            'keywords' => 'Reverse Logistics India, RTO Tracking, Return Shipping Management, Undelivered Parcel Reconciliation, E-Commerce Returns',
            'type' => 'service',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'platform.cod' => [
            'title' => 'Fast COD Remittance & Early Settlement | OneStall Cargo Platform',
            'description' => 'Maintain continuous working capital with OneStall Cargo\'s 1-2 day express COD remittance and transparent daily settlement reconciliation.',
            'keywords' => 'Fast COD Remittance India, Early COD Payout, Cash on Delivery Remittance Cycle, E-Commerce Cash Flow, Daily COD Reconciliation',
            'type' => 'service',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'platform.tracking' => [
            'title' => 'Real-Time Multi-Carrier Live Tracking | OneStall Cargo Platform',
            'description' => 'Provide customers with branded tracking pages, instant WhatsApp/SMS milestone notifications, and real-time multi-carrier delivery updates.',
            'keywords' => 'Real-Time Courier Tracking, Multi-Carrier Live Tracking, Branded Tracking Page, SMS Delivery Alerts, Consignment Milestones',
            'type' => 'service',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'platform.awb' => [
            'title' => 'Automated AWB Generation & Bulk Shipping Labels | OneStall Cargo',
            'description' => 'Generate compliant thermal barcode labels and AWB slips in bulk across multiple courier partners in a single click.',
            'keywords' => 'Automated AWB Generation, Shipping Label Generator, Thermal Barcode Print, Bulk AWB Printing, Courier Shipping Slips',
            'type' => 'service',
            'priority' => 0.7,
            'changefreq' => 'monthly',
        ],
        'privacy' => [
            'title' => 'Privacy Policy | OneStall Cargo Logistics',
            'description' => 'Official privacy policy of OneStall Cargo (Anyurva Ayurveda Private Limited). Learn how we protect personal, business, and shipment tracking data.',
            'keywords' => 'OneStall Privacy Policy, Logistics Data Protection, Shipping Information Security',
            'type' => 'website',
            'priority' => 0.3,
            'changefreq' => 'yearly',
        ],
        'terms' => [
            'title' => 'Terms of Service | OneStall Cargo Logistics',
            'description' => 'Official terms of service governing OneStall Cargo courier shipping, freight transport, wallet transactions, and platform usage.',
            'keywords' => 'OneStall Terms of Service, Logistics Terms, Shipping Contract India',
            'type' => 'website',
            'priority' => 0.3,
            'changefreq' => 'yearly',
        ],
        'cookies' => [
            'title' => 'Cookie Policy | OneStall Cargo Logistics',
            'description' => 'Learn how OneStall Cargo uses cookies and tracking technologies to optimize shipping dashboards, analytics, and user experience.',
            'keywords' => 'OneStall Cookie Policy, Website Cookies Logistics',
            'type' => 'website',
            'priority' => 0.3,
            'changefreq' => 'yearly',
        ],
        'blog.index' => [
            'title' => 'Logistics & Shipping Blog | OneStall Cargo Insights',
            'description' => 'Expert logistics guides, courier comparison tips, eCommerce shipping strategies, and cargo management advice from OneStall Cargo.',
            'keywords' => 'Logistics Blog India, Courier Service Guides, E-commerce Shipping Advice, How to Reduce Shipping Costs, B2B Cargo Guides',
            'type' => 'blog',
            'priority' => 0.9,
            'changefreq' => 'daily',
        ],
        'locations.delhi' => [
            'title' => 'Courier & Cargo Service in Delhi NCR | OneStall Central Hub Okhla',
            'description' => 'OneStall Cargo Delhi Central Hub in Okhla provides express B2C parcel delivery, B2B cargo distribution, and same-day pickups across Delhi, Noida, and Gurugram.',
            'keywords' => 'Courier Service in Delhi NCR, Delhi Cargo Hub, Okhla Logistics Center, Courier Service in South Delhi, Express Parcel Delivery Delhi',
            'type' => 'business.business',
            'priority' => 0.8,
            'changefreq' => 'weekly',
        ],
        'locations.lucknow' => [
            'title' => 'Courier & Cargo Services in Lucknow, UP | OneStall Headquarters',
            'description' => 'OneStall Cargo registered office and central Uttar Pradesh distribution hub in Lucknow. Seamless parcel delivery and bulk cargo across 75 UP districts.',
            'keywords' => 'Courier Service in Lucknow UP, Lucknow Logistics Hub, Uttar Pradesh Cargo Transport, Courier Company Lucknow, Registered Office UP',
            'type' => 'business.business',
            'priority' => 0.8,
            'changefreq' => 'weekly',
        ],
        'serviceability' => [
            'title' => 'Check Courier Serviceability & Pincodes | OneStall Cargo',
            'description' => 'Check courier serviceability and COD availability across 29,000+ Indian pincodes. Verify delivery coverage across metro, tier-2, and remote regions.',
            'keywords' => 'Courier Pincode Checker India, Deliverable Pincodes India, Courier Serviceability Search, Cash on Delivery Pincode List, OneStall Pincode Network',
            'type' => 'website',
            'priority' => 0.8,
            'changefreq' => 'weekly',
        ],
    ];

    /**
     * Get the current route's default metadata or fallback.
     */
    public function getCurrentMeta(): array
    {
        $routeName = Request::route() ? Request::route()->getName() : null;
        $path = trim(Request::path(), '/');

        if (!$routeName || $routeName === '' || $path === '') {
            $routeName = 'home';
        }

        if (isset($this->routeMeta[$routeName])) {
            return $this->routeMeta[$routeName];
        }

        // Path fallback mapping
        if (isset($this->routeMeta[$path])) {
            return $this->routeMeta[$path];
        }

        return [
            'title' => 'OneStall Cargo | Leading Logistics & Courier Solutions in India',
            'description' => 'OneStall Cargo is India\'s premier tech-enabled courier and logistics company covering B2C express shipping, B2B freight cargo, international delivery, and franchise hubs.',
            'keywords' => 'Courier Service in India, Shipping Company in India, Logistics Solutions, E-commerce Shipping India',
            'type' => 'website',
            'priority' => 0.5,
            'changefreq' => 'weekly',
        ];
    }

    /**
     * Get normalized absolute canonical URL.
     */
    public function getCanonicalUrl(?string $override = null): string
    {
        if ($override && filter_var($override, FILTER_VALIDATE_URL)) {
            return $override;
        }

        $path = Request::getPathInfo();
        $path = rtrim($path, '/');
        if ($path === '') {
            return $this->baseUrl;
        }

        return $this->baseUrl . $path;
    }

    /**
     * Get robots meta string.
     */
    public function getRobotsDirective(?string $override = null): string
    {
        if ($override) {
            return $override;
        }

        $path = Request::path();

        // Disallow private or transactional utility paths from indexing
        $noIndexPrefixes = [
            'admin', 'seller', 'hub', 'rider', 'api', 'dashboard',
            'login', 'register', 'forgot-password', 'reset-password', 'ndr/resolve'
        ];

        foreach ($noIndexPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return 'noindex, nofollow';
            }
        }

        return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }

    /**
     * Organization JSON-LD Schema
     */
    public function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->siteName,
            'legalName' => $this->corporateName,
            'url' => $this->baseUrl,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset($this->logoUrl),
                'width' => 250,
                'height' => 80,
            ],
            'image' => asset($this->defaultImage),
            'description' => 'OneStall Cargo is India\'s premier tech-enabled courier, cargo, and logistics platform providing automated multi-carrier B2C shipping, heavy B2B freight, and franchise hub networks.',
            'foundingDate' => '2024',
            'identifier' => [
                '@type' => 'PropertyValue',
                'propertyID' => 'CIN',
                'value' => $this->cin,
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '569/153 KHA, Barigawan, KP Road, 32 BN PAC',
                'addressLocality' => 'Lucknow',
                'addressRegion' => 'Uttar Pradesh',
                'postalCode' => '226023',
                'addressCountry' => 'IN',
            ],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => setting('site_phone', '+91 73764 90981'),
                    'contactType' => 'customer service',
                    'areaServed' => 'IN',
                    'availableLanguage' => ['English', 'Hindi'],
                ],
                [
                    '@type' => 'ContactPoint',
                    'telephone' => setting('site_phone', '+91 73764 90981'),
                    'contactType' => 'sales',
                    'areaServed' => 'IN',
                    'availableLanguage' => ['English', 'Hindi'],
                ]
            ],
            'sameAs' => array_values(array_filter([
                setting('facebook_url', 'https://www.facebook.com/onestallcargo'),
                setting('twitter_url', 'https://twitter.com/onestallcargo'),
                setting('linkedin_url', 'https://www.linkedin.com/company/onestallcargo'),
                setting('instagram_url', 'https://www.instagram.com/onestallcargo'),
                setting('youtube_url', 'https://www.youtube.com/@onestallcargoindia'),
            ], fn($url) => !empty($url) && $url !== '#'))
        ];
    }

    /**
     * WebSite JSON-LD Schema with SearchAction
     */
    public function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->siteName,
            'alternateName' => 'OneStall Cargo Logistics',
            'url' => $this->baseUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->baseUrl . '/track?awb_number={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ]
        ];
    }

    /**
     * BreadcrumbList JSON-LD Schema
     */
    public function breadcrumbSchema(array $items): array
    {
        $list = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        // Always prepend Home
        $position = 1;
        $list['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => 'Home',
            'item' => $this->baseUrl,
        ];

        foreach ($items as $name => $url) {
            $position++;
            $list['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $name,
                'item' => str_starts_with($url, 'http') ? $url : $this->baseUrl . '/' . ltrim($url, '/'),
            ];
        }

        return $list;
    }

    /**
     * Service JSON-LD Schema
     */
    public function serviceSchema(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $data['name'] ?? 'Logistics & Courier Service',
            'serviceType' => $data['type'] ?? 'Courier & Freight Shipping',
            'provider' => [
                '@type' => 'Organization',
                'name' => $this->siteName,
                'url' => $this->baseUrl,
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => $data['areaServed'] ?? 'India',
            ],
            'description' => $data['description'] ?? 'Express door-to-door courier and freight cargo delivery service across India.',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'INR',
                'availability' => 'https://schema.org/InStock',
                'description' => 'Pay-as-you-go commercial shipping rates with zero monthly platform fee.',
            ]
        ];
    }

    /**
     * FAQPage JSON-LD Schema (Compliant with Google guidelines)
     */
    public function faqSchema(iterable $faqs): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [],
        ];

        foreach ($faqs as $faq) {
            $q = is_array($faq) ? ($faq['question'] ?? $faq['q'] ?? '') : ($faq->question ?? '');
            $a = is_array($faq) ? ($faq['answer'] ?? $faq['a'] ?? '') : ($faq->answer ?? '');

            if ($q && $a) {
                $schema['mainEntity'][] = [
                    '@type' => 'Question',
                    'name' => strip_tags($q),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($a),
                    ]
                ];
            }
        }

        return $schema;
    }

    /**
     * Article JSON-LD Schema
     */
    public function articleSchema(array $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'] ?? '',
            'description' => $article['description'] ?? '',
            'image' => isset($article['image']) && $article['image'] ? asset($article['image']) : asset($this->defaultImage),
            'author' => [
                '@type' => 'Person',
                'name' => $article['author'] ?? 'OneStall Editorial Team',
                'jobTitle' => 'Senior Logistics Specialist',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset($this->logoUrl),
                ],
            ],
            'datePublished' => $article['published_at'] ?? date('c'),
            'dateModified' => $article['updated_at'] ?? date('c'),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $article['url'] ?? Request::url(),
            ]
        ];
    }

    /**
     * LocalBusiness JSON-LD Schema for verified operational hubs
     */
    public function localBusinessSchema(array $location): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $location['name'] ?? ($this->siteName . ' Hub'),
            'image' => asset($this->defaultImage),
            'telephone' => $location['phone'] ?? setting('site_phone', '+91 73764 90981'),
            'email' => $location['email'] ?? 'support@onestallcargo.com',
            'url' => $location['url'] ?? $this->baseUrl,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $location['address'] ?? '',
                'addressLocality' => $location['city'] ?? '',
                'addressRegion' => $location['state'] ?? '',
                'postalCode' => $location['pincode'] ?? '',
                'addressCountry' => 'IN',
            ],
            'openingHoursSpecification' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'opens' => '08:00',
                'closes' => '20:00',
            ],
            'priceRange' => '₹₹',
        ];
    }

    /**
     * Return all sitemap URLs with metadata
     */
    public function getSitemapEntries(): array
    {
        return $this->routeMeta;
    }
}
