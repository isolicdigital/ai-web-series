<?php
// routes/web.php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\DFYController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebSeriesController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

// ============================================
// PUBLIC ROUTES
// ============================================

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::get('/p', [PageBuilderController::class, 'maskedView'])->name('page-builder.view');
Route::post('/generate-single-scene-image/{sceneId}', [WebSeriesController::class, 'generateSingleSceneImage'])->name('generate.single.scene.image');
Route::post('/create-full-episode', [EpisodeController::class, 'createFullEpisode'])->name('create.full.episode');
Route::post('/check-image-status', [WebSeriesController::class, 'checkImageStatus'])->name('check.image.status');
Route::post('/generate-image-for-scene/{sceneId}', [WebSeriesController::class, 'generateImageForScene'])->name('generate.image.for.scene');
// Async image generation endpoint (non-blocking)
Route::get('/api/generate-scene-image-async/{sceneId}', [WebSeriesController::class, 'generateSceneImageAsyncBackground']);

// ============================================
// PROFILE & AUTH ROUTES
// ============================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/web-series/dashboard', [WebSeriesController::class, 'dashboard'])->name('web-series.dashboard');
    Route::post('/replicate-webhook', [WebSeriesController::class, 'handleReplicateWebhook'])->name('replicate.webhook');
});

// ============================================
// WEB SERIES ROUTES (Specific routes FIRST - NO PARAMETERS)
// ============================================

Route::middleware(['auth'])->group(function () {
    // Dashboard stats
    Route::get('/dashboard/stats', [WebSeriesController::class, 'getDashboardStats'])->name('dashboard.stats');
    Route::get('/dashboard/recent-series', [WebSeriesController::class, 'getRecentSeries'])->name('dashboard.recent-series');
    
    // Video generation routes
    Route::post('/generate-scene-video', [WebSeriesController::class, 'generateSceneVideo'])->name('generate.scene.video');
    Route::get('/api/scene/{sceneId}/video-status', [WebSeriesController::class, 'checkVideoStatus']);

    Route::get('/check-video-status/{sceneId}', [WebSeriesController::class, 'checkVideoStatus'])->name('check.video.status');
    Route::post('/generate-video', [WebSeriesController::class, 'generateSceneVideo'])->name('generate.video');
    // Async video generation endpoint
Route::post('/api/generate-video-async/{sceneId}', [WebSeriesController::class, 'generateVideoAsync']);
    
    // Scene status polling
    Route::get('/series/{id}/scenes-status', [WebSeriesController::class, 'getScenesStatus'])->name('series.scenes.status');
    
    // Image generation routes
    Route::post('/generate-image', [WebSeriesController::class, 'generateImage'])->name('generate.image');
    Route::post('/check-image-status', [WebSeriesController::class, 'checkImageStatus'])->name('check.image.status');
    // Add these routes after your auth middleware group
Route::post('/admin/retry-failed-images', [WebSeriesController::class, 'retryFailedImages'])
    ->middleware(['auth', 'admin'])
    ->name('admin.retry-failed-images');

Route::post('/web-series/retry-scene/{sceneId}', [WebSeriesController::class, 'retrySingleScene'])
    ->middleware('auth')
    ->name('web-series.retry-scene');
    
    // ============================================
    // IMPORTANT: Specific routes with NO parameters MUST come BEFORE parameterized routes
    // ============================================

    // Add this line with your other web-series routes (around line 70-80)
Route::get('/web-series/{seriesId}/episodes', [WebSeriesController::class, 'showEpisodes'])->name('web-series.episodes');
    
    // Specific routes (no {id} parameters)
    Route::get('/web-series/my-series', [WebSeriesController::class, 'mySeries'])->name('web-series.my-series');
    Route::get('/web-series/create', [WebSeriesController::class, 'create'])->name('web-series.create');
    Route::post('/web-series/save-project', [WebSeriesController::class, 'saveProject'])->name('web-series.save-project');
    
    // Episode CRUD routes - ADD THESE
    Route::post('/web-series/{seriesId}/episodes', [WebSeriesController::class, 'storeEpisode'])->name('web-series.store-episode');
Route::get('/web-series/{seriesId}/create-episode', [WebSeriesController::class, 'createEpisode'])->name('web-series.create-episode');
    Route::get('/web-series/{seriesId}/episodes/{episodeNumber}/edit', [WebSeriesController::class, 'editEpisode'])->name('web-series.edit-episode');
    Route::put('/web-series/{seriesId}/episodes/{episodeNumber}', [WebSeriesController::class, 'updateEpisode'])->name('web-series.update-episode');
    Route::delete('/web-series/{seriesId}/episodes/{episodeNumber}', [WebSeriesController::class, 'destroyEpisode'])->name('web-series.destroy-episode');
    
    // Episode merge route
    Route::post('/web-series/merge-episode', [EpisodeController::class, 'merge'])->name('merge.episode');
    
    // Get full video for an episode
    Route::get('/web-series/{series}/episodes/{episode}/full-video', [EpisodeController::class, 'getFullVideo'])->name('web-series.episode.full-video');
    
    // Parameterized routes (with {id}) - MUST come AFTER specific routes
    // Change this route to accept episode number as optional parameter
Route::get('/web-series/{id}/{episodeNumber?}', [WebSeriesController::class, 'show'])->name('web-series.show');;
    Route::get('/web-series/{seriesId}/episodes', [WebSeriesController::class, 'showEpisodes'])->name('web-series.episodes');
    Route::delete('/web-series/{id}', [WebSeriesController::class, 'destroy'])->name('web-series.destroy');
    Route::get('/web-series/{id}/generate-video', [WebSeriesController::class, 'generateVideoPage'])->name('web-series.generate-video-page');
    Route::post('/web-series/generate-video', [WebSeriesController::class, 'generateVideo'])->name('web-series.generate-video');
    
    // Episode and scene routes
    Route::post('/series/{id}/generate-episode-concept', [WebSeriesController::class, 'generateEpisodeConcept'])->name('web-series.generate-concept');
    Route::post('/series/{id}/update-episode-concept', [WebSeriesController::class, 'updateEpisodeConcept'])->name('web-series.update-concept');
    Route::post('/series/{id}/generate-episode-scenes', [WebSeriesController::class, 'generateEpisodeScenes'])->name('web-series.generate-scenes');
    Route::get('/web-series/{id}/episode-1', [WebSeriesController::class, 'showEpisode1'])->name('web-series.episode1');
    Route::get('/web-series/{seriesId}/scene/{sceneId}', [WebSeriesController::class, 'showScene'])->name('web-series.scene');
    Route::get('/web-series/{seriesId}/episodes/{episodeId}', function($seriesId, $episodeId) {
        $series = App\Models\WebSeries::where('user_id', auth()->id())->findOrFail($seriesId);
        $episode = App\Models\Episode::where('web_series_id', $seriesId)->where('id', $episodeId)->firstOrFail();
        return redirect()->route('web-series.show', $seriesId)->with('selected_episode', $episodeId);
    })->name('web-series.episode.show');
    
    Route::get('/web-series/{seriesId}/create-episode', [WebSeriesController::class, 'createEpisode'])->name('web-series.create-episode');
    // Add this route after your other episode routes
Route::get('/web-series/{seriesId}/episodes/{episodeNumber}', [WebSeriesController::class, 'showEpisode'])->name('web-series.episode.show');
Route::delete('/web-series/{seriesId}/episodes/{episodeNumber}', [WebSeriesController::class, 'destroyEpisode'])
    ->name('web-series.destroy-episode');
    Route::delete('/web-series/{id}', [WebSeriesController::class, 'destroy'])
    ->name('web-series.destroy');

});

// ============================================
// WEB SERIES ROUTES (Alternative with prefix)
// ============================================

Route::middleware(['auth'])->prefix('series')->name('web-series.')->group(function () {
    Route::get('/my-series', [WebSeriesController::class, 'mySeries']);
    Route::get('/create', [WebSeriesController::class, 'create']);
    Route::post('/save-project', [WebSeriesController::class, 'saveProject']);
    Route::get('/{id}', [WebSeriesController::class, 'show']);
    Route::delete('/{id}', [WebSeriesController::class, 'destroy']);
    Route::get('/{id}/generate-video', [WebSeriesController::class, 'generateVideoPage']);
    Route::post('/{id}/generate-episode1-concept', [WebSeriesController::class, 'generateEpisode1Concept']);
    Route::post('/{id}/update-episode1-concept', [WebSeriesController::class, 'updateEpisode1Concept']);
    Route::post('/{id}/generate-episode1-scenes', [WebSeriesController::class, 'generateEpisode1Scenes']);
    Route::get('/{id}/episode-1', [WebSeriesController::class, 'showEpisode1']);
    Route::get('/{seriesId}/scene/{sceneId}', [WebSeriesController::class, 'showScene']);
});

// ============================================
// DASHBOARD ROUTE
// ============================================

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// ============================================
// ADMIN ROUTES
// ============================================

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/image-logs', [WebSeriesController::class, 'imageLogs'])->name('image.logs');
});

// ============================================
// DEMO ROUTES (for user ID 141)
// ============================================

Route::middleware(['auth'])->prefix('demo')->name('demo.')->group(function () {
    // Video endpoints
    Route::get('/video/{episodeNumber}', [DemoController::class, 'getDemoVideo'])->name('video');
    Route::get('/episode/{episodeId}/full-video', [DemoController::class, 'getFullEpisodeVideo'])->name('episode.full-video');
    
    // Merge and generate endpoints
    Route::post('/merge-episode', [DemoController::class, 'mergeEpisode'])->name('merge-episode');
    Route::post('/generate-video/{sceneId}', [DemoController::class, 'generateVideo'])->name('generate-video');
    
    // Image endpoints
    Route::get('/image/{sceneNumber}', [DemoController::class, 'getDemoImage'])->name('image');
    
    // Series management
    Route::post('/create-series', [DemoController::class, 'createDemoSeries'])->name('create-series');
    Route::post('/generate-concept/{id}', [DemoController::class, 'generateConcept'])->name('generate-concept');
    Route::post('/generate-scenes/{id}', [DemoController::class, 'generateScenes'])->name('generate-scenes');
    
    // Dashboard
    Route::get('/dashboard-stats', [DemoController::class, 'getDashboardData'])->name('dashboard-stats');
    Route::get('/keywords', [DemoController::class, 'getAvailableKeywords'])->name('keywords');
});

// ============================================
// MAIN APPLICATION ROUTES (with subscription middleware)
// ============================================

Route::middleware(['auth', 'subscription'])->group(function () { 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/overview', function () { return view('page.overview'); })->name('overview');
    Route::get('/whitelabel', function () { return view('page.whitelabel'); })->name('whitelabel');
    
    // DFY Routes
    Route::prefix('dfy')->name('dfy.')->group(function () {
        Route::get('/', [DFYController::class, 'index'])->name('index');
        Route::get('/images', [DFYController::class, 'searchImage'])->name('images');
        Route::get('/videos', [DFYController::class, 'searchVideo'])->name('videos');
        Route::get('/audio', [DFYController::class, 'searchAudio'])->name('audio');
    });
    
    // Pricing & Credits
    Route::get('/buycredits', [PricingController::class, 'index'])->name('buycredits');
    
    // Page Builder Routes
    Route::prefix('page-builder')->name('page-builder.')->group(function () {
        Route::get('/', [PageBuilderController::class, 'savedPages'])->name('index');
        Route::get('/create-new/{cat?}', [PageBuilderController::class, 'createNew'])->name('create');
        Route::get('/dfy-templates/{cat?}', [PageBuilderController::class, 'dfy'])->name('dfy');
        Route::get('/site-cloner', [PageBuilderController::class, 'siteCloner'])->name('cloner');
        Route::post('/clone-from-url/{id}', [PageBuilderController::class, 'cloneFromUrl'])->name('clone');
        Route::get('/editor/{id}/{title}', [PageBuilderController::class, 'showEditor'])->name('show');
        Route::post('/editor/{id}/{title}/{cat}/{dir}', [PageBuilderController::class, 'saveEditor'])->name('save');
        Route::post('/save-asset/{id}', [PageBuilderController::class, 'saveAssets'])->name('assets');
        Route::get('/saves', [PageBuilderController::class, 'savedPages'])->name('saves');
        Route::get('/download/{id}', [PageBuilderController::class, 'downloadPage'])->name('download');
        Route::delete('/delete/{id}', [PageBuilderController::class, 'deletePage'])->name('delete');
    });  
});

// ============================================
// ADMIN ROUTES (Full admin panel)
// ============================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');
    
    // Admin Web Series Routes
    Route::prefix('series')->name('web-series.')->group(function () {
        Route::get('/', [WebSeriesController::class, 'adminIndex'])->name('index');
        Route::get('/{id}', [WebSeriesController::class, 'adminShow'])->name('show');
        Route::delete('/{id}', [WebSeriesController::class, 'adminDestroy'])->name('destroy');
    });
    
    // Temporary block route
    Route::get('/set-temp-block/{userId}/{minutes?}', function ($userId, $minutes = 10) {
        Cache::put('video_gen_block_' . $userId, $minutes * 60, $minutes * 60);
        return response()->json(['success' => true]);
    })->name('set.temp.block');
    
    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
    
    // Agency Management
    Route::prefix('agencies')->name('agencies.')->group(function () {
        Route::get('/', [AgencyController::class, 'index'])->name('index');
        Route::get('/create', [AgencyController::class, 'create'])->name('create');
        Route::post('/', [AgencyController::class, 'store'])->name('store');
        Route::get('/{agency}', [AgencyController::class, 'show'])->name('show');
        Route::get('/{agency}/edit', [AgencyController::class, 'edit'])->name('edit');
        Route::put('/{agency}', [AgencyController::class, 'update'])->name('update');
        Route::delete('/{agency}', [AgencyController::class, 'destroy'])->name('destroy');
    });

    // Plan Management
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
    });

    // Subscription Management
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
        Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');
    });
});

// ============================================
// SUPPORT PAGE
// ============================================

Route::get('/support', function () { return view('page.support'); })->name('support');

// ============================================
// FALLBACK ROUTE (404 page)
// ============================================

Route::fallback(function () { 
    return response()->view('errors.404', [], 404); 
});