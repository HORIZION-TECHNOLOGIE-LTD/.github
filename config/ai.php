<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configure AI model providers and default settings for the application.
    | Supported providers: OpenAI, Anthropic, Google
    |
    */

    // OpenAI Configuration
    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'organization' => env('OPENAI_ORGANIZATION', ''),
    ],

    // Anthropic Configuration
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY', ''),
    ],

    // Google AI Configuration
    'google' => [
        'api_key' => env('GOOGLE_API_KEY', ''),
    ],

    // Default Settings
    'defaults' => [
        'model' => env('AI_DEFAULT_MODEL', 'gpt-3.5-turbo'),
        'temperature' => env('AI_DEFAULT_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_DEFAULT_MAX_TOKENS', 1024),
    ],

    // Model to Provider Mapping
    'model_providers' => [
        'gpt-4' => 'openai',
        'gpt-3.5-turbo' => 'openai',
        'gpt-4-turbo' => 'openai',
        'claude-3-opus' => 'anthropic',
        'claude-3-sonnet' => 'anthropic',
        'claude-3-haiku' => 'anthropic',
        'gemini-pro' => 'google',
        'gemini-pro-vision' => 'google',
    ],

    // Model Display Names
    'model_names' => [
        'gpt-4' => 'GPT-4',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'claude-3-opus' => 'Claude 3 Opus',
        'claude-3-sonnet' => 'Claude 3 Sonnet',
        'claude-3-haiku' => 'Claude 3 Haiku',
        'gemini-pro' => 'Gemini Pro',
        'gemini-pro-vision' => 'Gemini Pro Vision',
    ],

    // Feature Flags
    'enabled' => env('AI_ENABLED', false),
    'placeholder_mode' => env('AI_PLACEHOLDER_MODE', true), // Return placeholders when true

];
