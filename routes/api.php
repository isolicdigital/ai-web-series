<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'standup', 'namespace' => 'App\Http\Controllers'], function () {
    Route::controller(AiStandUpController::class)->group(function () {
        Route::post('/webhook/face-swap', 'faceSwapWebhook')->name('standup.face-swap-webhook');
        Route::post('/webhook/script', 'scriptWebhook')->name('standup.script-webhook');
        Route::post('/webhook/video', 'videoWebhook')->name('standup.video-webhook');
    });
});
Route::post('/aigen/saveresponse', [\App\Http\Controllers\MlController::class, 'saveResponse'])->name('aigen.saveresponse');

Route::group(['prefix' => 'register', 'namespace' => 'App\Http\Controllers\Auth'], function () {
    Route::controller(RegisteredUserController::class)->group(function () {
        Route::get('/remote/{channel}', function () {
            abort(403, 'Method not allowed. Please use POST request.');
        });
        
        Route::post('/remote/{channel}', 'register_remote');
        Route::post('/test-email', 'testEmail');
        Route::get('/paid-tokens', function () {
            return response()->json(['message' => 'FORBIDDEN'], 403);
        });
        Route::post('/paid-tokens', 'handlePaidTokens')->name('paid-tokens');
    });
});