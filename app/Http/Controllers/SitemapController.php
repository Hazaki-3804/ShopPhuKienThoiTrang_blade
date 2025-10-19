<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(Request $request): Response
    {
        $baseUrl = rtrim(config('app.url') ?: $request->getSchemeAndHttpHost(), '/');

        $urls = [];

        // Static pages
        $urls[] = [
            'loc' => $baseUrl . '/',
            'changefreq' => 'daily',
            'priority' => '1.0',
            'lastmod' => now()->toAtomString(),
        ];
        $urls[] = [
            'loc' => $baseUrl . '/home',
            'changefreq' => 'daily',
            'priority' => '0.9',
            'lastmod' => now()->toAtomString(),
        ];
        $urls[] = [
            'loc' => $baseUrl . '/shop',
            'changefreq' => 'daily',
            'priority' => '0.8',
            'lastmod' => now()->toAtomString(),
        ];
        $urls[] = [
            'loc' => $baseUrl . '/about',
            'changefreq' => 'monthly',
            'priority' => '0.5',
            'lastmod' => now()->toAtomString(),
        ];
        $urls[] = [
            'loc' => $baseUrl . '/contact',
            'changefreq' => 'monthly',
            'priority' => '0.5',
            'lastmod' => now()->toAtomString(),
        ];

        // Category filtered pages via query parameter (?category=slug)
        Category::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($categories) use (&$urls, $baseUrl) {
                foreach ($categories as $cat) {
                    if (!empty($cat->slug)) {
                        $urls[] = [
                            'loc' => $baseUrl . '/shop?category=' . urlencode($cat->slug),
                            'changefreq' => 'weekly',
                            'priority' => '0.6',
                            'lastmod' => optional($cat->updated_at)->toAtomString() ?: now()->toAtomString(),
                        ];
                    }
                }
            });

        // Product detail pages (public, status=1)
        Product::query()
            ->where('status', 1)
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($products) use (&$urls, $baseUrl) {
                foreach ($products as $product) {
                    $urls[] = [
                        'loc' => $baseUrl . '/shop/' . $product->id,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'lastmod' => optional($product->updated_at)->toAtomString() ?: now()->toAtomString(),
                    ];
                }
            });

        // Build XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>' );
        foreach ($urls as $u) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($u['loc'], ENT_XML1));
            if (!empty($u['lastmod'])) {
                $url->addChild('lastmod', $u['lastmod']);
            }
            if (!empty($u['changefreq'])) {
                $url->addChild('changefreq', $u['changefreq']);
            }
            if (!empty($u['priority'])) {
                $url->addChild('priority', $u['priority']);
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }
}
