<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Services\Mcp\McpServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller for handling MCP (Model Context Protocol) requests.
 * 
 * This controller provides endpoints for AI agents to interact with
 * the ChiBank system using the Model Context Protocol.
 */
class McpController extends Controller
{
    /**
     * @var McpServerService
     */
    protected McpServerService $mcpService;

    /**
     * Create a new controller instance.
     */
    public function __construct(McpServerService $mcpService)
    {
        $this->mcpService = $mcpService;
    }

    /**
     * Handle incoming MCP request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('MCP Request received', ['method' => $data['method'] ?? 'unknown']);

        $response = $this->mcpService->handleRequest($data);

        return response()->json($response);
    }

    /**
     * Get MCP server info.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        return response()->json([
            'name' => config('mcp.server_name', 'ChiBank MCP Server'),
            'version' => config('mcp.server_version', '1.0.0'),
            'protocol_version' => '2024-11-05',
            'description' => 'MCP server for ChiBank wallet operations',
            'endpoints' => [
                'handle' => url('/mcp'),
                'info' => url('/mcp/info'),
                'sse' => url('/mcp/sse'),
            ],
            'capabilities' => [
                'tools' => true,
                'resources' => true,
                'prompts' => true,
            ],
        ]);
    }

    /**
     * Handle SSE (Server-Sent Events) connection for MCP.
     * 
     * Note: This is a simplified SSE implementation. For production use,
     * consider using a proper async solution like Laravel Octane, ReactPHP,
     * or a dedicated event streaming service.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function sse(Request $request)
    {
        return response()->stream(function () use ($request) {
            // Disable time limit for SSE connection
            set_time_limit(0);
            
            // Send initial connection event
            echo "event: connected\n";
            echo "data: " . json_encode(['status' => 'connected', 'server' => 'ChiBank MCP']) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            // Keep connection alive with periodic pings
            // Uses usleep for more efficient CPU usage
            $startTime = time();
            $timeout = 30; // 30 seconds timeout
            $pingInterval = 5; // seconds between pings
            $lastPing = $startTime;

            while (time() - $startTime < $timeout) {
                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }
                
                // Send keepalive ping every $pingInterval seconds
                if (time() - $lastPing >= $pingInterval) {
                    echo "event: ping\n";
                    echo "data: " . json_encode(['timestamp' => time()]) . "\n\n";
                    
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                    
                    $lastPing = time();
                }
                
                // Short sleep to reduce CPU usage without blocking too long
                usleep(100000); // 100ms
            }

            // Send close event
            echo "event: close\n";
            echo "data: " . json_encode(['reason' => 'timeout']) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
