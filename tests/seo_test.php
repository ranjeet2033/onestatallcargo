<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routesToTest = [
    '/' => 'Home',
    '/services' => 'Services',
    '/solutions/b2c-shipping' => 'B2C Shipping',
    '/solutions/b2b-cargo' => 'B2B Cargo',
    '/solutions/international-shipping' => 'International',
    '/solutions/quick-delivery' => 'Quick Delivery',
    '/solutions/ecommerce-integration' => 'Ecommerce',
    '/pricing' => 'Pricing Calculator',
    '/franchise' => 'Franchise',
    '/track' => 'Track',
    '/developers' => 'Developers',
    '/docs' => 'API Docs',
    '/corporate' => 'Corporate',
    '/partners' => 'Partners',
    '/about' => 'About Us',
    '/faq' => 'FAQs',
    '/help' => 'Help Center',
    '/contact' => 'Contact',
    '/privacy' => 'Privacy Policy',
    '/terms' => 'Terms of Service',
    '/cookies' => 'Cookie Policy',
    '/blog' => 'Blog Index',
    '/blog/how-to-choose-best-courier-service-india' => 'Blog Single Post',
    '/locations/delhi-ncr' => 'Delhi Hub',
    '/locations/lucknow-up' => 'Lucknow Hub',
    '/serviceability' => 'Serviceability Checker',
    '/sitemap.xml' => 'Dynamic XML Sitemap',
];

echo "========================================================\n";
echo "       ONESTALL CARGO SEO SUITE TESTING RUNNER          \n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

foreach ($routesToTest as $uri => $name) {
    try {
        $request = \Illuminate\Http\Request::create($uri, 'GET');
        $response = $app->handle($request);
        $status = $response->getStatusCode();
        $content = $response->getContent();

        $hasTitle = preg_match('/<title>(.*?)<\/title>/is', $content, $titleMatches);
        $hasCanonical = preg_match('/<link rel="canonical" href="(.*?)"/i', $content, $canonicalMatches);
        $hasDesc = preg_match('/<meta name="description" content="(.*?)"/i', $content, $descMatches);
        $hasSchema = str_contains($content, 'application/ld+json');
        $h1Count = preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $content, $h1Matches);

        if ($status === 200) {
            $passed++;
            $titleShort = $hasTitle ? substr($titleMatches[1], 0, 45) . '...' : 'NONE';
            $h1Info = ($uri === '/sitemap.xml') ? 'N/A' : "H1 Count: {$h1Count}";
            echo "[OK] {$status} | {$name} ({$uri})\n";
            echo "     Title:     {$titleShort}\n";
            echo "     Canonical: " . ($hasCanonical ? $canonicalMatches[1] : 'NONE') . "\n";
            echo "     Schema:    " . ($hasSchema ? 'YES' : 'NO') . " | {$h1Info}\n\n";
        } else {
            $failed++;
            echo "[FAIL] {$status} | {$name} ({$uri})\n\n";
        }
    } catch (\Throwable $e) {
        $failed++;
        echo "[ERROR] {$name} ({$uri}): " . $e->getMessage() . "\n\n";
    }
}

echo "========================================================\n";
echo "TOTAL TESTS: " . count($routesToTest) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "========================================================\n";
