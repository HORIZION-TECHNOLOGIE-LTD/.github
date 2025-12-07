<?php

use App\Http\Controllers\Api\AiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI API Routes
|--------------------------------------------------------------------------
|
| Routes for AI model integration with Vercel AI SDK.
| These endpoints provide chat, completion, and generation capabilities.
|
*/

Route::prefix('ai')->group(function () {
    // Health check
    Route::get('/health', [AiController::class, 'health'])->name('api.ai.health');
    
    // List available models
    Route::get('/models', [AiController::class, 'models'])->name('api.ai.models');
    
    // Chat endpoint (compatible with Vercel AI SDK)
    Route::post('/chat', [AiController::class, 'chat'])->name('api.ai.chat');
    
    // Text completion endpoint
    Route::post('/complete', [AiController::class, 'complete'])->name('api.ai.complete');
    
    // Content generation endpoint
    Route::post('/generate', [AiController::class, 'generate'])->name('api.ai.generate');
});
