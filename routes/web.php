<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AiStandUpController;
use App\Http\Controllers\ComedyController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\DFYController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';


//Public page builder page route
Route::get('/p', [PageBuilderController::class, 'maskedView'])->name('page-builder.view');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'subscription'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('comedy.index');
    })->name('dashboard');
    Route::get('overview', function () {
        return view('page.overview');
    })->name('overview');
    Route::get('whitelabel', function () {
        return view('page.whitelabel');
    })->name('whitelabel');
    Route::prefix('comedy')->name('comedy.')->group(function () {
        Route::get('/', [ComedyController::class, 'index'])->name('index');
        Route::get('/jokes/list', [ComedyController::class, 'jokes'])->name('jokes');
        Route::post('/generate', [ComedyController::class, 'generate'])->name('generate');
        Route::post('/generate-video', [ComedyController::class, 'generateVideo'])->name('generate-video');
        Route::get('/jokes', [ComedyController::class, 'getJokes'])->name('get-jokes');
        Route::delete('/joke/{id}', [ComedyController::class, 'deleteJoke'])->name('delete-joke');
        Route::delete('/video/{id}', [ComedyController::class, 'deleteVideo'])->name('delete-video');
        Route::get('/templates', [ComedyController::class, 'templates'])->name('templates');
        Route::get('/videos', [ComedyController::class, 'videos'])->name('videos');
        Route::get('/my-videos', [ComedyController::class, 'myVideos'])->name('my-videos');
    });
    Route::prefix('dfy')->name('dfy.')->group(function () {
        Route::get('/', [DFYController::class, 'index'])->name('index');
        Route::get('/images', [DFYController::class, 'searchImage'])->name('images');
        Route::get('/videos', [DFYController::class, 'searchVideo'])->name('videos');
        Route::get('/audio', [DFYController::class, 'searchAudio'])->name('audio');
    });
    Route::get('/buycredits', [PricingController::class, 'index'])->name('buycredits');
    Route::prefix('standup')->name('standup.')->group(function () {
        Route::get('/', [AiStandUpController::class, 'index'])->name('index');
        Route::get('/templates', [AiStandUpController::class, 'templates'])->name('templates');
        Route::get('/script', [AiStandUpController::class, 'scriptPage'])->name('script.page');
        Route::get('/video-generator', [AiStandUpController::class, 'videoGenerator'])->name('video.generator');
        
        Route::post('/select-comedian', [AiStandUpController::class, 'selectComedian'])->name('select.comedian');
        Route::post('/comedian/create', [AiStandUpController::class, 'createComedian'])->name('comedian.create');
        Route::get('/comedian/status/{trackId}', [AiStandUpController::class, 'comedianStatus'])->name('comedian.status');
        
        Route::post('/script/generate', [AiStandUpController::class, 'generateScript'])->name('script.generate');
        Route::get('/script/{id}', [AiStandUpController::class, 'getScript'])->name('script.get');
        Route::put('/script/{id}', [AiStandUpController::class, 'updateScript'])->name('script.update');
        Route::post('/script/{id}/regenerate', [AiStandUpController::class, 'regenerateScript'])->name('script.regenerate');
        
        Route::post('/video/generate', [AiStandUpController::class, 'generateStandUpVideo'])->name('video.generate');
        Route::get('/videos', [AiStandUpController::class, 'myStandUpVideos'])->name('videos.index');
    });
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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::get('/set-temp-block/{userId}/{minutes?}', function ($userId, $minutes = 10) {
        $cacheKey = 'video_gen_block_' . $userId;
        $seconds = $minutes * 60;
        Cache::put($cacheKey, $seconds, $seconds);
        
        return response()->json([
            'success' => true,
            'message' => "Temp block set for user {$userId} for {$minutes} minutes",
            'user_id' => $userId,
            'minutes' => $minutes
        ]);
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

Route::get('support', function () {
    return view('page.support');
})->name('support');