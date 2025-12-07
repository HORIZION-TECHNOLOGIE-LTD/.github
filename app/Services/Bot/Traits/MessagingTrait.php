<?php

namespace App\Services\Bot\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provides messaging capabilities for bots.
 * 
 * This trait can be combined with other traits for multiple inheritance.
 */
trait MessagingTrait
{
    /**
     * @var string|null The messaging API endpoint
     */
    protected ?string $messagingEndpoint = null;

    /**
     * @var string|null The API token for messaging
     */
    protected ?string $messagingToken = null;

    /**
     * Initialize messaging capabilities.
     *
     * @param string|null $endpoint
     * @param string|null $token
     * @return void
     */
    protected function initializeMessaging(?string $endpoint = null, ?string $token = null): void
    {
        $this->messagingEndpoint = $endpoint;
        $this->messagingToken = $token;
    }

    /**
     * Send a message to a recipient.
     *
     * @param string|int $recipient
     * @param string $message
     * @param array $options
     * @return array|null
     */
    public function sendMessage($recipient, string $message, array $options = []): ?array
    {
        if (empty($this->messagingEndpoint) || empty($this->messagingToken)) {
            $this->logMessagingError('Messaging not configured');
            return null;
        }

        try {
            $response = Http::withToken($this->messagingToken)
                ->post($this->messagingEndpoint, [
                    'recipient' => $recipient,
                    'message' => $message,
                    'options' => $options,
                ]);

            return $response->json();
        } catch (\Exception $e) {
            $this->logMessagingError('Send message failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle an incoming message.
     *
     * @param array $messageData
     * @return void
     */
    public function handleIncomingMessage(array $messageData): void
    {
        $senderId = $messageData['sender_id'] ?? null;
        $text = $messageData['text'] ?? '';

        if (empty($senderId)) {
            $this->logMessagingError('No sender ID in message');
            return;
        }

        // Check if it's a command
        if (str_starts_with($text, '/') && method_exists($this, 'executeCommand')) {
            $parts = explode(' ', $text);
            $command = ltrim(array_shift($parts), '/');
            $this->executeCommand($command, $parts, ['sender_id' => $senderId]);
        }
    }

    /**
     * Log messaging errors.
     *
     * @param string $message
     * @return void
     */
    protected function logMessagingError(string $message): void
    {
        $botId = $this->botId ?? 'unknown';
        Log::error("Bot [{$botId}] Messaging: {$message}");
    }
}
