<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Model Context Protocol (MCP) server integration.
    | MCP enables AI agents to interact with the ChiBank system.
    |
    */

    'server_name' => env('MCP_SERVER_NAME', 'ChiBank MCP Server'),

    'server_version' => env('MCP_SERVER_VERSION', '1.0.0'),

    'protocol_version' => env('MCP_PROTOCOL_VERSION', '2024-11-05'),

    /*
    |--------------------------------------------------------------------------
    | MCP Server Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the MCP server endpoints.
    |
    */

    'enabled' => env('MCP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | MCP Authentication
    |--------------------------------------------------------------------------
    |
    | Authentication settings for MCP endpoints.
    |
    */

    'auth' => [
        'enabled' => env('MCP_AUTH_ENABLED', false),
        'token' => env('MCP_AUTH_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting settings for MCP endpoints.
    |
    */

    'rate_limit' => [
        'enabled' => env('MCP_RATE_LIMIT_ENABLED', true),
        'max_requests' => env('MCP_RATE_LIMIT_MAX', 60),
        'decay_minutes' => env('MCP_RATE_LIMIT_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Logging
    |--------------------------------------------------------------------------
    |
    | Logging settings for MCP requests.
    |
    */

    'logging' => [
        'enabled' => env('MCP_LOGGING_ENABLED', true),
        'channel' => env('MCP_LOG_CHANNEL', 'stack'),
    ],

];
