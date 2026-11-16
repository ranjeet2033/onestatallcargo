# OneStall Cargo - Complete Technical SEO Audit Report
**Website:** [https://onestallcargo.com/](https://onestallcargo.com/)  
**Platform:** Laravel 12.x / PHP 8.2+ / Tailwind CSS / MySQL  
**Audit Date:** November 2026  
**Auditor:** Senior Technical SEO & Laravel Architecture Specialist  

---

## 1. Executive Summary

A comprehensive, codebase-level Technical SEO and Content Audit was conducted on the OneStall Cargo web platform. OneStall Cargo is an integrated B2B and B2C logistics aggregator and tech-enabled supply chain provider covering express courier services, freight cargo, cross-border shipping, franchise hub operations, and developer API integrations.

While the application features a modern visual design and robust logistics backend, several critical SEO deficiencies were severely hindering organic search indexing, crawl efficiency, keyword rankings, and B2B/B2C lead generation on Google.

### Primary Audit Findings at a Glance:
1. **Missing Dynamic XML Sitemap:** `https://onestallcargo.com/sitemap.xml` returns HTTP 404. Google Search Console has no machine-readable inventory of indexable pages.
2. **Deficient `robots.txt`:** The live `robots.txt` contained only two lines (`User-agent: * / Disallow:`), exposing admin, seller, hub, rider, and API routes to crawlers while failing to reference the sitemap.
3. **Missing Canonical Tags:** Zero pages implemented `<link rel="canonical">`, creating severe duplicate content vulnerability between HTTP/HTTPS, trailing slashes, and query parameters.
4. **Duplicate Meta Descriptions:** Over 20 public pages lacked `@section('meta_description')`, falling back to the exact same generic default site description.
5. **Multiple H1 Headings:** The homepage rendered multiple `<h1>` elements in the hero section.
6. **Zero Structured Data (JSON-LD):** The entire site contained zero Schema.org structured data (missing `Organization`, `WebSite`, `BreadcrumbList`, `Service`, `FAQPage`, and `Article`).
7. **Broken & Inconsistent Services Template:** The `/services` page extended `layouts.app` instead of `layouts.public`, leading to broken UI consistency, an unclosed `</div>` syntax error, and zero database-backed services rendered.
8. **Unoptimized Assets & Heavy Images:** Key promotional images exceeded 1 MB in raw JPEG format without WebP/AVIF modern formats, missing `loading="lazy"`, and lacking explicit dimension attributes.
9. **No Blog / Content Marketing Hub:** No content system existed to capture informational and high-intent commercial search queries (e.g., "courier service in India", "how to reduce shipping costs", "courier franchise in India").
10. **Thin / Placeholder Legal Content:** Legal pages (`/privacy`, `/terms`, `/cookies`) contained boilerplate "Lorem Ipsum" placeholder copy with identical titles.
11. **Empty FAQ Page:** The `/faq` template rendered "No FAQs available yet" because the database FAQ table was unseeded.

---

## 2. Codebase & Route Inspection

### 2.1 Route Architecture (`routes/web.php`)
The application defines 50+ routes, categorized as follows:

| Category | Routes | Indexable? | Previous SEO State |
| :--- | :--- | :--- | :--- |
| **Homepage** | `/` (`welcome`) | Yes | Missing canonical, 2 H1 tags, default meta |
| **Core Services** | `/services` | Yes | **Broken template**, wrong layout (`layouts.app`), empty DB |
| **Solutions** | `/solutions/b2c-shipping`, `/solutions/b2b-cargo`, `/solutions/international-shipping`, `/solutions/quick-delivery`, `/solutions/ecommerce-integration` | Yes | Unique titles, but **all shared duplicate meta description** |
| **Platform Tech** | `/platform/video-evidence`, `/platform/ndr-management`, `/platform/rto-management`, `/platform/cod-settlement`, `/platform/live-tracking`, `/platform/awb-labels` | Yes | Missing unique meta descriptions, missing schema |
| **Developer API** | `/developers`, `/docs` | Yes | High quality content, missing structured API/TechArticle schema |
| **Business / Franchise** | `/franchise`, `/corporate`, `/partners`, `/pricing` | Yes | High commercial intent, missing schema & unique descriptions |
| **Support & Utility** | `/track`, `/contact`, `/about`, `/faq`, `/help` | Yes | `/faq` empty; `/track` missing Sitelinks SearchBox schema |
| **Legal** | `/privacy`, `/terms`, `/cookies` | Yes | **Lorem Ipsum placeholder**, duplicate title/meta |
| **Private / Auth Portals** | `/admin/*`, `/seller/*`, `/hub/*`, `/rider/*`, `/login`, `/register`, `/forgot-password`, `/reset-password/*` | **No** | Exposed to search bots via permissive `robots.txt` |

### 2.2 Template & Layout Analysis
- **Main Public Layout:** `resources/views/layouts/public.blade.php`
  - Had hardcoded fallback meta tags:
    - `og:description` and `twitter:description` duplicated the default meta description across every page.
    - Missing `<link rel="canonical" href="...">`.
    - Missing `<meta name="robots" content="...">`.
    - Missing `<meta name="keywords" content="...">`.
    - Missing JSON-LD structured data block.
    - Missing breadcrumb UI component.
  - Legacy layout `resources/views/layouts/app.blade.php` was erroneously used by `services.blade.php`.

---

## 3. Detailed Audit Findings by SEO Category

### 3.1 Metadata & On-Page SEO
| Page URL | Current Title | Meta Description Status | Primary H1 Status |
| :--- | :--- | :--- | :--- |
| `/` | OneStall Cargo \| Leading Logistics & Courier Solutions | Unique | **Critical: 2 H1 tags detected** |
| `/services` | Services - OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/solutions/b2c-shipping` | B2C Shipping Solutions \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/solutions/b2b-cargo` | B2B & Cargo Shipping Solutions \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/solutions/international-shipping` | International Shipping Solutions \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/solutions/quick-delivery` | Quick Delivery & Hyperlocal Solutions \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/solutions/ecommerce-integration` | E-commerce Shipping & Integrations \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/platform/video-evidence` | Video Evidence & Fraud Protection \| OneStall Cargo Platform | **Missing (Duplicate Default)** | Present (1 H1) |
| `/platform/ndr-management` | NDR Management & RTO Reduction \| OneStall Cargo Platform | **Missing (Duplicate Default)** | Present (1 H1) |
| `/platform/rto-management` | RTO Management & Reverse Logistics \| OneStall Cargo Platform | **Missing (Duplicate Default)** | Present (1 H1) |
| `/platform/cod-settlement` | Early COD Remittance & Settlement \| OneStall Cargo Platform | **Missing (Duplicate Default)** | Present (1 H1) |
| `/platform/live-tracking` | Real-time GPS & Courier Live Tracking \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/platform/awb-labels` | Automated AWB Generation & Shipping Labels \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/pricing` | Calculate Shipping Rates \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/franchise` | Logistics Franchise & Partner Hub Program \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/corporate` | Corporate & Enterprise Logistics \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/partners` | Logistics & Transport Partner Network \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/developers` | Developer Platform & Shipping APIs \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/docs` | RESTful Shipping API Documentation \| OneStall Cargo | Unique | Present (1 H1) |
| `/about` | About Us \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/faq` | Frequently Asked Questions (FAQ) \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/help` | Help Center & Seller Support \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/track` | Track Shipment \| OneStall Cargo | **Missing (Duplicate Default)** | Present (1 H1) |
| `/privacy` | OneStall Cargo \| Leading Logistics & Courier Company | **Missing (Duplicate Default)** | Present (1 H1) |
| `/terms` | OneStall Cargo \| Leading Logistics & Courier Company | **Missing (Duplicate Default)** | Present (1 H1) |
| `/cookies` | OneStall Cargo \| Leading Logistics & Courier Company | **Missing (Duplicate Default)** | Present (1 H1) |

### 3.2 Indexing & Crawlability
1. **robots.txt**: Currently allows crawling of admin portals (`/admin`), seller dashboards (`/seller`), hub dashboards (`/hub`), and rider app (`/rider`). Crawlers waste budget scanning login-gated URLs.
2. **sitemap.xml**: Completely absent. Google cannot discover new pages or track update timestamps (`lastmod`).
3. **Canonicalization**: Absent across all templates. If URLs are accessed via `http://`, `https://`, `https://www.`, or with UTM tracking parameters, search engines could index multiple duplicate variations.

### 3.3 Structured Data (JSON-LD)
Zero Schema.org types were found in the codebase. Missing schemas:
- `Organization`: Registered name (ANYURVA AYURVEDA PRIVATE LIMITED), brand (OneStall Cargo), CIN, registered address, official contact details.
- `WebSite`: SearchAction for shipment tracking.
- `BreadcrumbList`: Hierarchical categorization across solutions, platform, and developers.
- `Service`: Formal schema for logistics services (express shipping, B2B freight, international delivery).
- `FAQPage`: Rich snippet FAQs compliant with Google search guidelines.
- `Article`: Structured blog post markup.

### 3.4 Image Optimization & Performance (Core Web Vitals)
- **Large File Sizes:**
  - `public/images/warehouse.jpg`: 1,046 KB (1.05 MB)
  - `public/images/logistics_hero_banner.jpg`: 948 KB
  - `public/images/hero_b2b.jpg`: 769 KB
  - `public/images/pricing_calculator.jpg`: 745 KB
  - `public/images/dashboard.jpg`: 671 KB
  - `public/images/mobile_app.jpg`: 632 KB
  - `public/images/pricing_hero.jpg`: 611 KB
  - `public/images/ecommerce_boy.jpg`: 513 KB
- **Missing WebP/AVIF Support:** No modern formats in use.
- **Missing Lazy Loading:** Above-the-fold and below-the-fold images all load synchronously, degrading LCP.
- **Browser Caching:** `.htaccess` lacks fine-grained `ExpiresDefault` and `Cache-Control` headers for static images, stylesheets, and scripts.

---

## 4. Priority Action Items (Roadmap to Implementation)

1. **Architecture:** Create an `App\Services\SeoService` to dynamically serve metadata, canonicals, robots directives, Open Graph, Twitter cards, and Schema.org JSON-LD.
2. **Sitemap & Robots:** Build dynamic XML sitemap controller (`/sitemap.xml`) and update `public/robots.txt`.
3. **Layout Overhaul:** Update `layouts/public.blade.php` with dynamic SEO hooks, breadcrumbs, and preconnect tags.
4. **Services Page:** Migrate `resources/views/services.blade.php` to `layouts.public`, fix HTML syntax, and seed database services.
5. **Database Seeding:** Populate `services` and `faqs` tables with authentic, rich logistics data.
6. **Page Optimization:** Audit and rewrite titles, meta descriptions, and heading structures across all public service, solution, platform, developer, and legal pages.
7. **Blog System:** Build complete blog architecture (`/blog` and `/blog/{slug}`) with 8 comprehensive, original educational articles with Article schema.
8. **Local SEO:** Implement verified location hubs (`/locations/delhi-ncr`, `/locations/lucknow-up`) and serviceability checker (`/serviceability`).
9. **Image Optimization:** Convert all heavy assets in `public/images` to WebP, add lazy loading and keyword-rich alt tags.
10. **Performance:** Configure Apache browser caching in `public/.htaccess`.
