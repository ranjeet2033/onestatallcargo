# OneStall Cargo - Post-Launch SEO Action Items & Pending Tasks

**Website:** [https://onestallcargo.com/](https://onestallcargo.com/)  
**Document Purpose:** Clear checklist of external configurations, account-level verifications, tracking setups, and ongoing organic search maintenance requiring domain or third-party platform access.

---

## 1. Google Search Console (GSC) Setup (Requires Account Access)

The technical codebase is 100% prepared for Google Search Console. The site administrator must perform the following actions once deployed to the production server:

- [ ] **1.1 Domain Ownership Verification:**
  - Add property `https://onestallcargo.com/` in [Google Search Console](https://search.google.com/search-console).
  - Verify via DNS TXT record (recommended) or HTML meta tag.
  - *Codebase Support:* If using HTML tag verification, add the verification string in the Admin Settings panel (`google_site_verification`) or in `.env`, and it will automatically be injected into the `<head>` of all pages via `layouts/public.blade.php`.
- [ ] **1.2 Submit Dynamic XML Sitemap:**
  - In GSC, navigate to **Indexing &rarr; Sitemaps**.
  - Enter `sitemap.xml` and click **Submit**.
  - Verify that the status reports **Success** and all 31+ URLs are discovered.
- [ ] **1.3 URL Inspection & Priority Request Indexing:**
  - In GSC, use the **URL Inspection** tool to inspect:
    - `https://onestallcargo.com/`
    - `https://onestallcargo.com/services`
    - `https://onestallcargo.com/solutions/b2c-shipping`
    - `https://onestallcargo.com/solutions/b2b-cargo`
    - `https://onestallcargo.com/solutions/international-shipping`
    - `https://onestallcargo.com/franchise`
    - `https://onestallcargo.com/pricing`
    - `https://onestallcargo.com/serviceability`
  - Click **Request Indexing** on these primary commercial target pages to accelerate initial crawler discovery.
- [ ] **1.4 Monitor Index Coverage:**
  - Review the **Pages** report weekly for any crawl anomalies, soft 404s, or canonical mismatches.
- [ ] **1.5 Set Up Security & Core Web Vitals Monitoring:**
  - Monitor the **Core Web Vitals** report (LCP, INP, CLS) across Mobile and Desktop device segments.

---

## 2. Google Analytics 4 (GA4) & Lead Conversion Tracking

- [ ] **2.1 Configure GA4 Measurement ID:**
  - Create a GA4 property for `OneStall Cargo` in [Google Analytics](https://analytics.google.com/).
  - Obtain the Measurement ID (format: `G-XXXXXXXXXX`).
  - Add the Measurement ID to Admin Settings (`ga_measurement_id`) or deployment configuration.
- [ ] **2.2 Configure Lead Conversion Events:**
  - Track commercial conversion actions in GA4:
    - `generate_lead_contact`: Triggered upon successful submission of `/contact`.
    - `franchise_inquiry`: Triggered upon submission of the `/franchise` application form.
    - `sign_up_start` and `sign_up_complete`: Triggered on `/register` completion.
    - `rate_calculation_used`: Triggered when a visitor calculates courier rates on `/pricing`.
    - `awb_tracked`: Triggered when a consignment query is submitted on `/track`.

---

## 3. Google Business Profile (GBP) / Local SEO Listings

- [ ] **3.1 Claim & Verify Delhi Central Hub:**
  - Claim listing on Google Business Profile:
    - **Name:** OneStall Cargo - Delhi Central Hub
    - **Category:** Courier Service / Freight Forwarding Service / Logistics Service
    - **Address:** Warehouse B-12, Okhla Industrial Area, Phase 1, South Delhi, New Delhi – 110020
    - **Website Link:** `https://onestallcargo.com/locations/delhi-ncr`
    - **Phone:** +91 73764 90981
    - **Hours:** Mon–Sat: 08:00 AM – 08:00 PM
- [ ] **3.2 Claim & Verify Corporate Headquarters (Lucknow):**
  - Claim listing on Google Business Profile:
    - **Name:** OneStall Cargo - Anyurva Ayurveda Private Limited
    - **Category:** Corporate Office / Logistics Service
    - **Address:** 569/153 KHA, Barigawan, KP Road, 32 BN PAC, Lucknow – 226023, Uttar Pradesh
    - **Website Link:** `https://onestallcargo.com/locations/lucknow-up`
    - **Phone:** +91 73764 90981

---

## 4. Bing Webmaster Tools & Alternate Search Engines

- [ ] **4.1 Bing Webmaster Tools Submission:**
  - Connect `https://onestallcargo.com/` to [Bing Webmaster Tools](https://www.bing.com/webmasters) (can be instantly imported from Google Search Console).
  - Submit `https://onestallcargo.com/sitemap.xml`.
- [ ] **4.2 IndexNow Protocol Activation:**
  - Enable IndexNow key in production to instantly notify Bing, Yandex, and Naver whenever new blog articles or service pages are updated.

---

## 5. Ongoing Content Cadence & Off-Page SEO Roadmap

- [ ] **5.1 Monthly Blog Cadence:**
  - Publish 2 to 4 high-value educational articles per month targeting long-tail eCommerce logistics keywords (e.g., "Best Courier Service for Shopify in India", "How to Handle RTO in Festive Season", "Hyperlocal vs Express Delivery Comparison").
- [ ] **5.2 Backlink & Digital PR Outreach:**
  - Acquire high-authority industry backlinks from Indian eCommerce hubs (YourStory, Inc42, Entrackr, Retail4Growth).
  - List OneStall Cargo on reputable B2B directories: IndiaMart, JustDial, TradeIndia, and Clutch.
- [ ] **5.3 Periodic Technical Audits:**
  - Re-run `php tests/seo_test.php` prior to any major frontend or routing release to ensure zero regression in metadata, canonicals, or structured data schemas.
