<?php

use App\Http\Controllers\Bot\McpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MCP (Model Context Protocol) Routes
|--------------------------------------------------------------------------
|
| Routes for the Model Context Protocol server integration.
| These endpoints enable AI agents to interact with ChiBank.
|
*/

// MCP Server endpoints
Route::prefix('mcp')->group(function () {
    // Main MCP endpoint for JSON-RPC requests
    Route::post('/', [McpController::class, 'handle'])->name('mcp.handle');
    
    // Server info endpoint
    Route::get('/info', [McpController::class, 'info'])->name('mcp.info');
    
    // SSE endpoint for real-time updates
    Route::get('/sse', [McpController::class, 'sse'])->name('mcp.sse');
});
