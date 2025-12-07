<?php

namespace App\Services\Bot\Contracts;

/**
 * Interface for bots that can send and receive messages.
 */
interface MessagingCapable
{
    /**
     * Send a message to a recipient.
     *
     * @param string|int $recipient The recipient identifier
     * @param string $message The message content
     * @param array $options Additional options
     * @return array|null The response from the messaging operation
     */
    public function sendMessage($recipient, string $message, array $options = []): ?array;

    /**
     * Handle an incoming message.
     *
     * @param array $messageData The message data
     * @return void
     */
    public function handleIncomingMessage(array $messageData): void;
}
