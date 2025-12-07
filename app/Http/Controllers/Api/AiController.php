<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * AI Integration Controller
 * 
 * This controller provides API endpoints for AI model integration
 * compatible with Vercel AI SDK patterns and templates.
 */
class AiController extends Controller
{
    /**
     * Get supported model IDs for validation
     * 
     * @return array
     */
    private function getSupportedModels(): array
    {
        return array_keys(config('ai.model_providers', []));
    }

    /**
     * List available AI models
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function models()
    {
        $modelProviders = config('ai.model_providers', []);
        $modelNames = config('ai.model_names', []);
        
        $models = [];
        
        foreach ($modelProviders as $modelId => $provider) {
            $models[] = [
                'id' => $modelId,
                'name' => $modelNames[$modelId] ?? ucfirst($modelId),
                'provider' => $provider,
                'available' => !empty(config("ai.{$provider}.api_key")),
            ];
        }

        return response()->json([
            'models' => $models,
            'message' => 'Available AI models',
        ]);
    }

    /**
     * Chat with AI model
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        $supportedModels = $this->getSupportedModels();
        
        $validator = Validator::make($request->all(), [
            'messages' => 'required|array',
            'messages.*.role' => 'required|string|in:user,assistant,system',
            'messages.*.content' => 'required|string',
            'model' => 'nullable|string|in:' . implode(',', $supportedModels),
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $messages = $request->input('messages');
        $model = $request->input('model') ?? config('ai.defaults.model') ?? 'gpt-3.5-turbo';
        $temperature = $request->input('temperature') ?? config('ai.defaults.temperature') ?? 0.7;
        $maxTokens = $request->input('max_tokens') ?? config('ai.defaults.max_tokens') ?? 1024;

        try {
            // Check if AI integration is properly configured
            $apiKey = $this->getApiKeyForModel($model);
            
            if (empty($apiKey)) {
                return response()->json([
                    'error' => 'AI model not configured',
                    'message' => 'Please configure the API key for this model in your environment variables.',
                    'model' => $model,
                ], 503);
            }

            // Check if we're in placeholder mode
            if (config('ai.placeholder_mode', true)) {
                return response()->json([
                    'id' => 'chatcmpl-' . uniqid(),
                    'object' => 'chat.completion',
                    'created' => time(),
                    'model' => $model,
                    'choices' => [
                        [
                            'index' => 0,
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'This is a placeholder response. To enable actual AI integration, set AI_PLACEHOLDER_MODE=false and configure your AI provider SDK.',
                            ],
                            'finish_reason' => 'stop',
                        ],
                    ],
                    'usage' => [
                        'prompt_tokens' => 0,
                        'completion_tokens' => 0,
                        'total_tokens' => 0,
                    ],
                    'meta' => [
                        'placeholder' => true,
                        'message' => 'Enable AI_PLACEHOLDER_MODE=false to use real AI providers',
                    ],
                ]);
            }

            // Return a structured response compatible with Vercel AI SDK
            return response()->json([
                'id' => 'chatcmpl-' . uniqid(),
                'object' => 'chat.completion',
                'created' => time(),
                'model' => $model,
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'This is a placeholder response. Please integrate with your preferred AI provider (OpenAI, Anthropic, Google) using their respective SDKs.',
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'AI processing failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Text completion endpoint
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(Request $request)
    {
        $supportedModels = $this->getSupportedModels();
        
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'model' => 'nullable|string|in:' . implode(',', $supportedModels),
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $prompt = $request->input('prompt');
        $model = $request->input('model') ?? config('ai.defaults.model') ?? 'gpt-3.5-turbo';

        try {
            $apiKey = $this->getApiKeyForModel($model);
            
            if (empty($apiKey)) {
                return response()->json([
                    'error' => 'AI model not configured',
                    'message' => 'Please configure the API key for this model in your environment variables.',
                ], 503);
            }

            if (config('ai.placeholder_mode', true)) {
                return response()->json([
                    'id' => 'cmpl-' . uniqid(),
                    'object' => 'text_completion',
                    'created' => time(),
                    'model' => $model,
                    'choices' => [
                        [
                            'text' => 'This is a placeholder completion. Set AI_PLACEHOLDER_MODE=false to enable actual AI integration.',
                            'index' => 0,
                            'finish_reason' => 'stop',
                        ],
                    ],
                    'meta' => [
                        'placeholder' => true,
                    ],
                ]);
            }

            return response()->json([
                'id' => 'cmpl-' . uniqid(),
                'object' => 'text_completion',
                'created' => time(),
                'model' => $model,
                'choices' => [
                    [
                        'text' => 'This is a placeholder completion. Please integrate with your preferred AI provider.',
                        'index' => 0,
                        'finish_reason' => 'stop',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Completion Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'AI processing failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate content with AI
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $supportedModels = $this->getSupportedModels();
        
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'type' => 'nullable|string|in:text,image,code',
            'model' => 'nullable|string|in:' . implode(',', $supportedModels),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $prompt = $request->input('prompt');
        $type = $request->input('type', 'text');
        $model = $request->input('model') ?? config('ai.defaults.model') ?? 'gpt-3.5-turbo';

        try {
            if (config('ai.placeholder_mode', true)) {
                return response()->json([
                    'id' => 'gen-' . uniqid(),
                    'type' => $type,
                    'model' => $model,
                    'content' => 'Generated content placeholder. Set AI_PLACEHOLDER_MODE=false to enable actual AI generation.',
                    'created' => time(),
                    'meta' => [
                        'placeholder' => true,
                    ],
                ]);
            }
            return response()->json([
                'id' => 'gen-' . uniqid(),
                'type' => $type,
                'model' => $model,
                'content' => 'Generated content placeholder. Integrate with your AI provider for actual generation.',
                'created' => time(),
            ]);
        } catch (\Exception $e) {
            Log::error('AI Generation Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'AI generation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Health check endpoint
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        $providers = [
            'openai' => !empty(config('ai.openai.api_key')),
            'anthropic' => !empty(config('ai.anthropic.api_key')),
            'google' => !empty(config('ai.google.api_key')),
        ];

        return response()->json([
            'status' => 'ok',
            'service' => 'ChiBank AI Integration',
            'providers' => $providers,
            'placeholder_mode' => config('ai.placeholder_mode', true),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get API key for a specific model
     * 
     * @param string $model
     * @return string|null
     */
    private function getApiKeyForModel(string $model): ?string
    {
        // Get provider from configuration
        $provider = config("ai.model_providers.{$model}");
        
        if (!$provider) {
            return null;
        }
        
        // Get API key for the provider
        return config("ai.{$provider}.api_key");
    }
}
