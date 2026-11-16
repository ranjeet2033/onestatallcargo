<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Service;
use Illuminate\Database\Seeder;

class SeoContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Core Logistics Services
        $services = [
            [
                'title' => 'B2C Express Courier Shipping',
                'slug' => 'b2c-express-courier',
                'icon' => 'fa-solid fa-box',
                'description' => 'Fast, door-to-door express parcel delivery across 29,000+ pin codes in India. Powered by automated multi-carrier allocation, 1-2 day express COD remittance, and proactive NDR resolution to slash RTO losses.',
                'is_active' => true,
            ],
            [
                'title' => 'B2B Cargo & Bulk Freight Transport',
                'slug' => 'b2b-cargo-freight',
                'icon' => 'fa-solid fa-truck-moving',
                'description' => 'Cost-effective heavy freight transport for commercial inventory, raw materials, and retail goods across India. Full truckload (FTL) and less-than-truckload (LTL) services with discounted commercial freight rates and transit insurance.',
                'is_active' => true,
            ],
            [
                'title' => 'International Air Courier Services',
                'slug' => 'international-courier',
                'icon' => 'fa-solid fa-plane',
                'description' => 'Global cross-border parcel delivery to over 220 countries and territories. Transparent customs clearance support, Duty Delivered Paid (DDP) options, and seamless international air freight tracking.',
                'is_active' => true,
            ],
            [
                'title' => 'Same-Day & Hyperlocal Quick Delivery',
                'slug' => 'same-day-hyperlocal',
                'icon' => 'fa-solid fa-bolt',
                'description' => 'Intra-city express courier service and on-demand 2-hour hyperlocal bike dispatch for local retail stores, pharmacies, and D2C brands. Live GPS rider map and instant proof of delivery.',
                'is_active' => true,
            ],
            [
                'title' => 'Courier Franchise & Logistics Hubs',
                'slug' => 'courier-franchise-hub',
                'icon' => 'fa-solid fa-store',
                'description' => 'High-ROI business partnership opportunity for entrepreneurs. Own exclusive district delivery rights, booking counters, and master sorting hubs with 35-45% profit margins and complete operational software.',
                'is_active' => true,
            ],
            [
                'title' => 'Logistics API & Store Integrations',
                'slug' => 'logistics-api-integration',
                'icon' => 'fa-solid fa-code',
                'description' => 'Developer-friendly RESTful shipping APIs and pre-built connectors for Shopify, WooCommerce, Magento, and Amazon. Automate rate calculation, bulk AWB generation, label printing, and webhook status sync.',
                'is_active' => true,
            ],
        ];

        foreach ($services as $srv) {
            Service::updateOrCreate(
                ['slug' => $srv['slug']],
                $srv
            );
        }

        // 2. Seed High-Value Logistics FAQs
        $faqs = [
            [
                'category' => 'shipping',
                'question' => 'How does OneStall Cargo allocate the best courier partner for each shipment?',
                'answer' => 'OneStall Cargo uses an automated rule-based courier allocation engine that analyzes historical delivery success rates, pickup transit speed, cost efficiency, and real-time pincode performance among 15+ integrated carrier partners (including Delhivery, BlueDart, XpressBees, and Shadowfax) to assign the optimal courier for every order.',
                'sort_order' => 1,
            ],
            [
                'category' => 'shipping',
                'question' => 'Which pin codes and geographical areas are covered in India?',
                'answer' => 'OneStall Cargo covers over 29,000+ pin codes across all 28 states and 8 union territories in India, including Tier-1 metropolitan cities, Tier-2/3 industrial towns, and remote Northeast/island regions with both prepaid and Cash on Delivery (COD) services.',
                'sort_order' => 2,
            ],
            [
                'category' => 'shipping',
                'question' => 'What is the key difference between B2C express shipping and B2B cargo services?',
                'answer' => 'B2C shipping is engineered for direct-to-consumer parcels up to 5-10 kg with doorstep delivery, COD collection, and rapid transit times. B2B cargo is designed for commercial freight, palletized shipments, and bulk inventory movements (10 kg to 10+ tons) utilizing LTL/FTL trucking with E-Way Bill compliance and commercial tax documentation.',
                'sort_order' => 3,
            ],
            [
                'category' => 'cod_remittance',
                'question' => 'How fast are Cash on Delivery (COD) payments remitted to sellers?',
                'answer' => 'Standard COD remittance occurs on a rapid 1-2 day turnaround cycle following successful parcel delivery. Unlike traditional courier services that withhold COD funds for 7-14 days, OneStall Cargo provides automated daily reconciliations directly to your verified bank account.',
                'sort_order' => 4,
            ],
            [
                'category' => 'cod_remittance',
                'question' => 'Is early COD settlement available for high-volume eCommerce merchants?',
                'answer' => 'Yes. Qualified high-volume shippers can activate Early COD Remittance from their seller portal, enabling immediate payout settlement upon delivery confirmation to ensure steady business cash flow.',
                'sort_order' => 5,
            ],
            [
                'category' => 'ndr_rto',
                'question' => 'How does the automated NDR management system reduce RTO rates by up to 40%?',
                'answer' => 'When a courier records an undelivered attempt (customer unavailable, incorrect address, fake remark), OneStall Cargo immediately triggers an automated multichannel notification (WhatsApp, SMS, and automated IVR calling) to verify customer availability and reschedule reattempt delivery before the parcel returns to origin.',
                'sort_order' => 6,
            ],
            [
                'category' => 'rates_billing',
                'question' => 'Are there any upfront setup costs or recurring monthly subscription fees?',
                'answer' => 'No. OneStall Cargo operates on a pure pay-as-you-go model with zero monthly subscription fees, zero platform maintenance charges, and zero minimum order commitments. You only pay for the shipments you send.',
                'sort_order' => 7,
            ],
            [
                'category' => 'rates_billing',
                'question' => 'How is volumetric weight calculated for courier and cargo shipments?',
                'answer' => 'Volumetric weight is computed as (Length x Width x Height in cm) / 5000 for standard express couriers, and / 4000 or / 4500 for specialized commercial cargo depending on the freight carrier mode. Chargeable weight is the higher of actual gross weight or volumetric weight.',
                'sort_order' => 8,
            ],
            [
                'category' => 'franchise',
                'question' => 'How can an entrepreneur apply for a OneStall Cargo courier franchise?',
                'answer' => 'You can submit an online franchise application via our Franchise Portal. Applications are reviewed within 48 hours for territory availability. Approved partners receive exclusive district delivery rights, training, barcode scanners, and access to our centralized logistics software.',
                'sort_order' => 9,
            ],
            [
                'category' => 'franchise',
                'question' => 'What is the typical investment and return on investment (ROI) for a franchise hub?',
                'answer' => 'Franchise partners generally see a 35% to 45% return on investment within 3 to 6 months depending on local shipment volume, delivery density, and commercial booking counter turnover.',
                'sort_order' => 10,
            ],
            [
                'category' => 'api_tech',
                'question' => 'Can developers integrate OneStall Cargo APIs into custom software or mobile apps?',
                'answer' => 'Yes. We provide complete RESTful JSON APIs for live rate estimation, shipment booking, bulk AWB generation, manifest dispatch, and webhook event notifications with sub-100ms response times and complete Postman/OpenAPI documentation.',
                'sort_order' => 11,
            ],
            [
                'category' => 'api_tech',
                'question' => 'What is the Video Evidence platform and how does it prevent courier fraud?',
                'answer' => 'The Video Evidence feature allows merchants and hub operators to record timestamped packaging videos at dispatch stations and hub handover points. This tamper-proof visual audit trail eliminates 98% of fake courier damage remarks and weight discrepancy disputes.',
                'sort_order' => 12,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'category' => $faq['category'],
                    'is_active' => true,
                    'sort_order' => $faq['sort_order'],
                ]
            );
        }
    }
}
