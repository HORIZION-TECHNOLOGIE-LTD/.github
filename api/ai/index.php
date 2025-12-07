<?php

/**
 * AI Integration API for Vercel AI SDK
 * 
 * This endpoint provides AI model integration for the ChiBank application,
 * compatible with Vercel AI SDK templates and patterns.
 */

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Load Laravel application
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle the request
$request = Request::capture();

try {
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (\Exception $e) {
    // If there's an error, return a helpful response
    $response = response()->json([
        'message' => 'AI API endpoint error',
        'error' => $e->getMessage(),
        'endpoints' => [
            '/api/ai/health' => 'GET - Health check',
            '/api/ai/models' => 'GET - List available AI models',
            '/api/ai/chat' => 'POST - Chat with AI model',
            '/api/ai/complete' => 'POST - Text completion',
            '/api/ai/generate' => 'POST - Generate content',
        ],
        'documentation' => 'https://sdk.vercel.ai/docs',
    ], 500);
    
    $response->send();
}
