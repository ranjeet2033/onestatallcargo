<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\SeoService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Generate dynamic XML sitemap
     */
    public function index(): Response
    {
        $baseUrl = 'https://onestallcargo.com';
        $entries = $this->seoService->getSitemapEntries();
        $urls = [];

        foreach ($entries as $key => $meta) {
            // Determine URL from route name or path
            if ($key === 'home') {
                $loc = $baseUrl;
            } elseif (Route::has($key)) {
                $loc = route($key);
            } else {
                $loc = $baseUrl . '/' . ltrim($key, '/');
            }

            // Normalise to https://onestallcargo.com
            $loc = preg_replace('#^http://[^/]+#', $baseUrl, $loc);

            $urls[] = [
                'loc' => $loc,
                'lastmod' => date('Y-m-d'),
                'changefreq' => $meta['changefreq'] ?? 'weekly',
                'priority' => number_format((float)($meta['priority'] ?? 0.7), 1, '.', ''),
            ];
        }

        // Add verified location pages
        $locations = ['delhi-ncr', 'lucknow-up'];
        foreach ($locations as $locSlug) {
            $urls[] = [
                'loc' => $baseUrl . '/locations/' . $locSlug,
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Add serviceability check page
        $urls[] = [
            'loc' => $baseUrl . '/serviceability',
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];

        // Add Blog index
        $urls[] = [
            'loc' => $baseUrl . '/blog',
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        // Add dynamic blog posts if model exists and table exists
        try {
            if (class_exists(\App\Models\BlogPost::class) && \Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
                $posts = \App\Models\BlogPost::where('is_published', true)->get();
                foreach ($posts as $post) {
                    $urls[] = [
                        'loc' => $baseUrl . '/blog/' . $post->slug,
                        'lastmod' => $post->updated_at ? $post->updated_at->format('Y-m-d') : date('Y-m-d'),
                        'changefreq' => 'monthly',
                        'priority' => '0.8',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silently fall back to core static URLs if blog table is not yet migrated
            \Illuminate\Support\Facades\Log::warning('Sitemap dynamic blog query skipped: ' . $e->getMessage());
        }

        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $u) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "        <lastmod>" . $u['lastmod'] . "</lastmod>\n";
            $xml .= "        <changefreq>" . $u['changefreq'] . "</changefreq>\n";
            $xml .= "        <priority>" . $u['priority'] . "</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex, follow', // Do not index the sitemap itself as a webpage
        ]);
    }
}
