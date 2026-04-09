<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\StockSearch;
use Illuminate\Support\Facades\Log;

class DFYController extends Controller
{
    /**
     * Display DFY dashboard
     */
    public function index()
    {
        $page_title = 'Done For You Content';
        return view('dfy.index', compact('page_title'));
    }

    /**
     * Search for images
     */
    public function searchImage(Request $request)
    {
        $page_title = 'DFY Visuals';
        $prompt = $request->input('prompt');
        $orientation = $request->input('orientation', 'all');
        $page = $request->input('page', 1);
        $per_page = $request->input('per_page', 20);
        
        $images = ['images' => [], 'total' => 0, 'total_pages' => 0];
        
        if ($prompt) {
            $result = StockSearch::search_images($prompt, $per_page, $page, $orientation);
            
            // Handle different return formats
            if (!empty($result)) {
                if (isset($result['images'])) {
                    // Pexels/Pixabay format (already has images array)
                    $images = $result;
                } elseif (is_array($result) && !isset($result['images'])) {
                    // If result is directly an array of images
                    $images['images'] = $result;
                    $images['total'] = count($result);
                    $images['total_pages'] = ceil(count($result) / $per_page);
                }
            }
        }
        
        return view('dfy.search-image', compact('page_title', 'prompt', 'images', 'orientation', 'page', 'per_page'));
    }

    /**
     * Search for videos
     */
    public function searchVideo(Request $request)
    {
        $page_title = 'DFY Footages';
        $prompt = $request->input('prompt');
        $type = $request->input('type', 'landscape');
        $page = $request->input('page', 1);
        $per_page = $request->input('per_page', 12); // Reduced from 20 to 12
        
        $videos = [];
        $total = 0;
        $total_pages = 0;
        
        if ($prompt) {
            $result = StockSearch::search_videos($prompt, $per_page, $page, $type);
            $videos = $result['videos'] ?? $result;
            $total = $result['total'] ?? count($videos);
            $total_pages = $result['total_pages'] ?? ceil($total / $per_page);
        }
        
        return view('dfy.search-video', compact('page_title', 'prompt', 'type', 'videos', 'page', 'per_page', 'total', 'total_pages'));
    }

    /**
     * Search for audio
     */
    public function searchAudio(Request $request)
    {
        $page_title = 'DFY Audio';
        $prompt = $request->input('prompt');
        $audios = [];
        
        if ($prompt) {
            $audios = StockSearch::search_media('music', $prompt);
        }
        
        return view('dfy.search-audio', compact('page_title', 'prompt', 'audios'));
    }
}