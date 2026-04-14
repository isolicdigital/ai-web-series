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

// ============================================
// PROFILE ROUTES
// ============================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// WEB SERIES ROUTES
// ============================================

Route::middleware(['auth'])->prefix('series')->name('web-series.')->group(function () {
    Route::get('/create', [WebSeriesController::class, 'create'])->name('create');
    Route::post('/save-project', [WebSeriesController::class, 'saveProject'])->name('save-project');
    Route::post('/{id}/generate-episode1-concept', [WebSeriesController::class, 'generateEpisode1Concept']);
    Route::post('/{id}/update-episode1-concept', [WebSeriesController::class, 'updateEpisode1Concept']);
    Route::post('/{id}/generate-episode1-scenes', [WebSeriesController::class, 'generateEpisode1Scenes']);
    Route::get('/{id}/episode-1', [WebSeriesController::class, 'showEpisode1'])->name('episode1');
    Route::get('/{seriesId}/scene/{sceneId}', [WebSeriesController::class, 'showScene'])->name('scene');
    Route::get('/my-series', [WebSeriesController::class, 'mySeries'])->name('my-series');
    Route::get('/dashboard', [WebSeriesController::class, 'dashboard'])->name('dashboard');
    Route::get('/{id}', [WebSeriesController::class, 'show'])->name('show');
    Route::delete('/{id}', [WebSeriesController::class, 'destroy'])->name('destroy');
     Route::get('/{id}/generate-video', [WebSeriesController::class, 'generateVideoPage'])->name('generate-video');
    Route::post('/generate-video', [WebSeriesController::class, 'generateVideo'])->name('generate.video');
});

// ============================================
// IMAGE GENERATION ROUTES
// ============================================

// Image Generation Routes
Route::middleware(['auth'])->post('/generate-image', [WebSeriesController::class, 'generateImage']);
Route::middleware(['auth'])->post('/check-image-status', [WebSeriesController::class, 'checkImageStatus']);
// routes/web.php
Route::middleware(['auth', 'admin'])->get('/image-logs', [WebSeriesController::class, 'imageLogs'])->name('image.logs');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
// ============================================
// MAIN APPLICATION ROUTES
// ============================================

Route::middleware(['auth', 'subscription'])->group(function () { 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('overview', function () { return view('page.overview'); })->name('overview');
    Route::get('whitelabel', function () { return view('page.whitelabel'); })->name('whitelabel');
    
    Route::prefix('dfy')->name('dfy.')->group(function () {
        Route::get('/', [DFYController::class, 'index'])->name('index');
        Route::get('/images', [DFYController::class, 'searchImage'])->name('images');
        Route::get('/videos', [DFYController::class, 'searchVideo'])->name('videos');
        Route::get('/audio', [DFYController::class, 'searchAudio'])->name('audio');
    });
    
    Route::get('/buycredits', [PricingController::class, 'index'])->name('buycredits');
    
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
// ADMIN ROUTES
// ============================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');
    
    Route::prefix('series')->name('web-series.')->group(function () {
        Route::get('/', [WebSeriesController::class, 'adminIndex'])->name('index');
        Route::get('/{id}', [WebSeriesController::class, 'adminShow'])->name('show');
        Route::delete('/{id}', [WebSeriesController::class, 'adminDestroy'])->name('destroy');
    });
    
    Route::get('/set-temp-block/{userId}/{minutes?}', function ($userId, $minutes = 10) {
        Cache::put('video_gen_block_' . $userId, $minutes * 60, $minutes * 60);
        return response()->json(['success' => true]);
    })->name('set.temp.block');
    
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
    
    Route::prefix('agencies')->name('agencies.')->group(function () {
        Route::get('/', [AgencyController::class, 'index'])->name('index');
        Route::get('/create', [AgencyController::class, 'create'])->name('create');
        Route::post('/', [AgencyController::class, 'store'])->name('store');
        Route::get('/{agency}', [AgencyController::class, 'show'])->name('show');
        Route::get('/{agency}/edit', [AgencyController::class, 'edit'])->name('edit');
        Route::put('/{agency}', [AgencyController::class, 'update'])->name('update');
        Route::delete('/{agency}', [AgencyController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
    });

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

Route::get('support', function () { return view('page.support'); })->name('support');

Route::fallback(function () { 
    return response()->view('errors.404', [], 404); 
});