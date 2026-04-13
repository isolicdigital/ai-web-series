<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\WebSeries;
use App\Models\Scene;
use App\Models\Episode;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user's series - count scenes directly without relationship
        $webSeries = WebSeries::where('user_id', $user->id)
            ->latest()
            ->get();
        
        // Manually count scenes for each series
        foreach ($webSeries as $series) {
            $series->scenes_count = Scene::where('web_series_id', $series->id)->count();
        }
        
        // Calculate stats
        $mySeriesCount = $webSeries->count();
        
        // Get scenes count directly
        $myScenesCount = Scene::whereIn('web_series_id', $webSeries->pluck('id'))->count();
        
        // Calculate completion rate
        $completedSeries = $webSeries->where('status', 'completed')->count();
        $completionRate = $mySeriesCount > 0 ? round(($completedSeries / $mySeriesCount) * 100) : 0;
        
        // Global stats
        $totalSeries = WebSeries::where('status', 'completed')->count();
        $totalUsers = User::count();
        $totalScenesGenerated = Scene::count();
        $totalEpisodes = Episode::count();
        $avgRating = 4.9;
        
        return view('web-series.dashboard', compact(
            'webSeries',
            'mySeriesCount',
            'myScenesCount',
            'completionRate',
            'totalSeries',
            'totalUsers',
            'totalScenesGenerated',
            'totalEpisodes',
            'avgRating'
        ));
    }
}