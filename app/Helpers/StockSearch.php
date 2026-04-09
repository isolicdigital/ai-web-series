<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class StockSearch
{
    private const PEXELS_BASE_URL = 'https://api.pexels.com/v1/';
    
    private const UNSPLASH_API_KEY = 'YOUR_UNSPLASH_API_KEY'; // Replace with your key
    private const UNSPLASH_BASE_URL = 'https://api.unsplash.com/';
    
    public static function search_media($type, $search): array
    {
        $slug = $type === 'music' ? 'stock-music/discover' : 'sound-effects';
        $search = str_replace(' ', '-', $search);
        $url = "https://mixkit.co/free-$slug/$search";
    
        $response = Http::get($url);
    
        if ($response->failed()) {
            return [];
        }
    
        $crawler = new Crawler($response->body());
        $audios = [];
    
        $crawler->filter('div.item-grid-card--show-meta')->each(function (Crawler $node) use (&$audios) {
            $title = $node->filter('h2.item-grid-card__title')->text();
            $url = $node->children()->first()->attr('data-audio-player-preview-url-value');
    
            $audios[] = compact('title', 'url');
        });
    
        return $audios;
    }

    public static function search_pexel($type, $slug)
    {
        // Normalize slug
        $slug = str_replace(' ', '-', $slug);
        $slug = match ($slug) {
            'lofi' => 'music-beat',
            'royaltyfree' => 'nature',
            default => $slug,
        };

        // Build the URL
        $url = "https://geminimodel.softprolab.com/pxe.php?slug={$slug}&type={$type}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)',
            ])->withoutVerifying()->get($url);

            if (!$response->successful()) {
                return [];
            }

            $video_result = $response->object();
            $vdata = [];

            if (!empty($video_result->videos)) {
                foreach ($video_result->videos as $vid) {
                    $vdata[] = [
                        'video' => $vid->video_files[0]->link ?? '',
                        'thumb' => $vid->image ?? '',
                    ];
                }
            }

            return $vdata;
        } catch (\Exception $e) {
            \Log::error('Pexel fetch failed: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search for images on Pexels
     */
    public static function search_pexels_images(
        string $query, 
        int $per_page = 20, 
        int $page = 1,
        string $orientation = 'landscape'
    ): array {
        try {
            $response = Http::withHeaders([
                'Authorization' => env('PEXELS_KEY'),
            ])->get(self::PEXELS_BASE_URL . 'search', [
                'query' => $query,
                'per_page' => $per_page,
                'page' => $page,
                'orientation' => $orientation,
            ]);

            if ($response->failed()) {
                \Log::error('Pexels API request failed: ' . $response->status());
                return [];
            }

            $data = $response->json();

            if (!isset($data['photos']) || empty($data['photos'])) {
                return [];
            }

            $images = [];
            foreach ($data['photos'] as $photo) {
                $images[] = [
                    'id' => $photo['id'],
                    'preview' => $photo['src']['tiny'],
                    'webformat' => $photo['src']['medium'],
                    'large' => $photo['src']['large'],
                    'fullhd' => $photo['src']['large2x'],
                    'original' => $photo['src']['original'],
                    'tags' => $query,
                    'photographer' => $photo['photographer'],
                    'photographer_url' => $photo['photographer_url'],
                    'width' => $photo['width'],
                    'height' => $photo['height'],
                    'avg_color' => $photo['avg_color'],
                ];
            }

            return [
                'images' => $images,
                'total' => $data['total_results'] ?? 0,
                'total_pages' => ceil(($data['total_results'] ?? 0) / $per_page),
                'current_page' => $page,
                'per_page' => $per_page,
            ];

        } catch (\Exception $e) {
            \Log::error('Pexels search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search for images on Unsplash
     */
    public static function search_unsplash_images(
        string $query, 
        int $per_page = 20, 
        int $page = 1,
        string $orientation = 'landscape'
    ): array {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . self::UNSPLASH_API_KEY,
            ])->get(self::UNSPLASH_BASE_URL . 'search/photos', [
                'query' => $query,
                'per_page' => $per_page,
                'page' => $page,
                'orientation' => $orientation,
            ]);

            if ($response->failed()) {
                \Log::error('Unsplash API request failed: ' . $response->status());
                return [];
            }

            $data = $response->json();

            if (!isset($data['results']) || empty($data['results'])) {
                return [];
            }

            $images = [];
            foreach ($data['results'] as $photo) {
                $images[] = [
                    'id' => $photo['id'],
                    'preview' => $photo['urls']['thumb'],
                    'webformat' => $photo['urls']['small'],
                    'large' => $photo['urls']['regular'],
                    'fullhd' => $photo['urls']['full'],
                    'original' => $photo['urls']['raw'],
                    'tags' => $query,
                    'description' => $photo['description'] ?? $photo['alt_description'] ?? '',
                    'photographer' => $photo['user']['name'],
                    'photographer_url' => $photo['user']['links']['html'],
                    'width' => $photo['width'],
                    'height' => $photo['height'],
                    'likes' => $photo['likes'],
                ];
            }

            return [
                'images' => $images,
                'total' => $data['total'] ?? 0,
                'total_pages' => ceil(($data['total'] ?? 0) / $per_page),
                'current_page' => $page,
                'per_page' => $per_page,
            ];

        } catch (\Exception $e) {
            \Log::error('Unsplash search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fallback: Search for images on Pixabay
     */
    public static function search_pixabay(
        string $query, 
        int $per_page = 20, 
        int $page = 1, 
        string $image_type = 'all',
        string $orientation = 'all',
        string $category = '',
        string $min_width = '',
        string $min_height = '',
        array $colors = [],
        bool $editors_choice = false,
        bool $safesearch = true,
        string $order = 'popular'
    ): array {
        try {
            $params = [
                'key' => '16608468-ff7378e46e02287180efb87d8',
                'q' => urlencode($query),
                'per_page' => $per_page,
                'page' => $page,
                'image_type' => $image_type,
                'orientation' => $orientation,
                'safesearch' => $safesearch ? 'true' : 'false',
                'order' => $order,
            ];

            if (!empty($category)) $params['category'] = $category;
            if (!empty($min_width)) $params['min_width'] = $min_width;
            if (!empty($min_height)) $params['min_height'] = $min_height;
            if (!empty($colors)) $params['colors'] = implode(',', $colors);
            if ($editors_choice) $params['editors_choice'] = 'true';

            $response = Http::timeout(30)->get('https://pixabay.com/api/', $params);

            if ($response->failed() || !isset($response->json()['hits'])) {
                return [];
            }

            $data = $response->json();
            $images = [];

            foreach ($data['hits'] as $hit) {
                $images[] = [
                    'id' => $hit['id'],
                    'preview' => $hit['previewURL'],
                    'webformat' => $hit['webformatURL'],
                    'large' => $hit['largeImageURL'],
                    'fullhd' => $hit['fullHDURL'] ?? $hit['largeImageURL'],
                    'tags' => $hit['tags'],
                    'views' => $hit['views'],
                    'downloads' => $hit['downloads'],
                    'likes' => $hit['likes'],
                    'comments' => $hit['comments'],
                    'user' => $hit['user'],
                    'user_image' => $hit['userImageURL'],
                    'width' => $hit['imageWidth'],
                    'height' => $hit['imageHeight'],
                    'type' => $hit['type'],
                ];
            }

            return [
                'images' => $images,
                'total' => $data['totalHits'] ?? 0,
                'total_pages' => ceil(($data['totalHits'] ?? 0) / $per_page),
                'current_page' => $page,
                'per_page' => $per_page,
            ];

        } catch (\Exception $e) {
            \Log::error('Pixabay search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Main image search method with fallback
     */
    public static function search_images(string $query, int $per_page = 20, int $page = 1, string $orientation = 'all'): array
    {
        // Convert orientation for Pexels (landscape, portrait, square)
        $pexelOrientation = 'landscape';
        if ($orientation === 'vertical') {
            $pexelOrientation = 'portrait';
        } elseif ($orientation === 'all') {
            $pexelOrientation = 'landscape';
        }
        
        // Try Pexels first
        $result = self::search_pexels_images($query, $per_page, $page, $pexelOrientation);
        
        if (!empty($result['images'])) {
            return $result;
        }
        
        // Fallback to Pixabay
        return self::search_pixabay($query, $per_page, $page, 'all', $orientation);
    }


    /**
     * Search for videos on Pexels
     */
    public static function search_pexels_videos(
        string $query, 
        int $per_page = 12, 
        int $page = 1,
        string $orientation = 'landscape'
    ): array {
        try {
            $response = Http::withHeaders([
                'Authorization' => env('PEXELS_KEY'),
            ])->get(self::PEXELS_BASE_URL . 'videos/search', [
                'query' => $query,
                'per_page' => $per_page,
                'page' => $page,
                'orientation' => $orientation,
            ]);

            if ($response->failed()) {
                return ['videos' => [], 'total' => 0, 'total_pages' => 0];
            }

            $data = $response->json();
            $videos = [];

            foreach ($data['videos'] as $video) {
                $videoFiles = $video['video_files'];
                $bestVideo = end($videoFiles);
                
                $videos[] = [
                    'id' => $video['id'],
                    'video' => $bestVideo['link'],
                    'thumb' => $video['image'],
                    'duration' => $video['duration'],
                    'width' => $bestVideo['width'],
                    'height' => $bestVideo['height'],
                    'user' => $video['user']['name'],
                ];
            }

            return [
                'videos' => $videos,
                'total' => $data['total_results'] ?? 0,
                'total_pages' => ceil(($data['total_results'] ?? 0) / $per_page),
            ];

        } catch (\Exception $e) {
            return ['videos' => [], 'total' => 0, 'total_pages' => 0];
        }
    }

    /**
     * Fallback: Search for videos on Pixabay
     */
    public static function search_pixabay_videos(
        string $query, 
        int $per_page = 20, 
        int $page = 1,
        string $orientation = 'all'
    ): array {
        try {
            $params = [
                'key' => '16608468-ff7378e46e02287180efb87d8',
                'q' => urlencode($query),
                'per_page' => $per_page,
                'page' => $page,
                'video_type' => 'all',
            ];

            $response = Http::timeout(30)->get('https://pixabay.com/api/videos/', $params);

            if ($response->failed() || !isset($response->json()['hits'])) {
                return [];
            }

            $data = $response->json();
            $videos = [];

            foreach ($data['hits'] as $hit) {
                // Get the medium quality video (or largest available)
                $videos[] = [
                    'id' => $hit['id'],
                    'video' => $hit['videos']['medium']['url'] ?? $hit['videos']['large']['url'] ?? '',
                    'thumb' => $hit['videos']['tiny']['thumbnail'] ?? '',
                    'duration' => $hit['duration'],
                    'width' => $hit['videos']['medium']['width'] ?? 0,
                    'height' => $hit['videos']['medium']['height'] ?? 0,
                    'tags' => $hit['tags'],
                    'user' => $hit['user'],
                ];
            }

            return $videos;

        } catch (\Exception $e) {
            \Log::error('Pixabay video search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Main video search method with fallback
     */
    public static function search_videos(string $query, int $per_page = 20, int $page = 1, string $orientation = 'landscape'): array
    {
        // Try Pexels first
        $result = self::search_pexels_videos($query, $per_page, $page, $orientation);
        
        if (!empty($result)) {
            return $result;
        }
        
        // Fallback to Pixabay
        return self::search_pixabay_videos($query, $per_page, $page, $orientation);
    }
}