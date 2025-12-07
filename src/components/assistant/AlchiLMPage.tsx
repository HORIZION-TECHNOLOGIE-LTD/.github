import React, { useState, useCallback, useRef, useEffect } from 'react';

/**
 * AlchiLM Page Component
 * 
 * A conversational AI assistant interface for the ChiBank platform.
 * Provides intelligent assistance for banking operations and queries.
 */

interface Message {
  id: string;
  role: 'user' | 'assistant' | 'system';
  content: string;
  timestamp: Date;
}

interface AlchiLMPageProps {
  apiEndpoint?: string;
  welcomeMessage?: string;
  placeholder?: string;
  maxHistoryLength?: number;
}

const DEFAULT_WELCOME_MESSAGE = 'Hello! I am AlchiLM, your intelligent banking assistant. How can I help you today?';
const DEFAULT_PLACEHOLDER = 'Type your message here...';

const generateId = (): string => {
  return `msg_${Date.now()}_${Math.random().toString(36).slice(2, 11)}`;
};

export const AlchiLMPage: React.FC<AlchiLMPageProps> = ({
  apiEndpoint = '/api/assistant/chat',
  welcomeMessage = DEFAULT_WELCOME_MESSAGE,
  placeholder = DEFAULT_PLACEHOLDER,
  maxHistoryLength = 100,
}) => {
  const [messages, setMessages] = useState<Message[]>([
    {
      id: generateId(),
      role: 'assistant',
      content: welcomeMessage,
      timestamp: new Date(),
    },
  ]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLTextAreaElement>(null);

  // Auto-scroll to bottom when new messages arrive
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  // Focus input on mount
  useEffect(() => {
    inputRef.current?.focus();
  }, []);

  const sendMessage = useCallback(async () => {
    const trimmedInput = inputValue.trim();
    if (!trimmedInput || isLoading) return;

    const userMessage: Message = {
      id: generateId(),
      role: 'user',
      content: trimmedInput,
      timestamp: new Date(),
    };

    setMessages((prev) => {
      const newMessages = [...prev, userMessage];
      // Trim history if needed
      if (newMessages.length > maxHistoryLength) {
        return newMessages.slice(-maxHistoryLength);
      }
      return newMessages;
    });

    setInputValue('');
    setIsLoading(true);
    setError(null);

    try {
      const response = await fetch(apiEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          message: trimmedInput,
          history: messages.map((m) => ({
            role: m.role,
            content: m.content,
          })),
        }),
      });

      if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
      }

      const data = await response.json();
      
      const assistantMessage: Message = {
        id: generateId(),
        role: 'assistant',
        content: data.response || data.message || 'I apologize, but I could not process your request.',
        timestamp: new Date(),
      };

      setMessages((prev) => [...prev, assistantMessage]);
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An unexpected error occurred';
      setError(errorMessage);
      
      const errorResponse: Message = {
        id: generateId(),
        role: 'assistant',
        content: 'I apologize, but I encountered an error processing your request. Please try again later.',
        timestamp: new Date(),
      };
      
      setMessages((prev) => [...prev, errorResponse]);
    } finally {
      setIsLoading(false);
    }
  }, [inputValue, isLoading, messages, apiEndpoint, maxHistoryLength]);

  const handleKeyDown = useCallback(
    (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    },
    [sendMessage]
  );

  const clearChat = useCallback(() => {
    setMessages([
      {
        id: generateId(),
        role: 'assistant',
        content: welcomeMessage,
        timestamp: new Date(),
      },
    ]);
    setError(null);
  }, [welcomeMessage]);

  const formatTimestamp = (date: Date): string => {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  return (
    <div className="alchi-lm-container">
      <div className="alchi-lm-header">
        <h1 className="alchi-lm-title">
          <span className="alchi-lm-icon">🤖</span>
          AlchiLM Assistant
        </h1>
        <button
          className="alchi-lm-clear-btn"
          onClick={clearChat}
          title="Clear chat history"
        >
          Clear Chat
        </button>
      </div>

      <div className="alchi-lm-messages">
        {messages.map((message) => (
          <div
            key={message.id}
            className={`alchi-lm-message alchi-lm-message--${message.role}`}
          >
            <div className="alchi-lm-message-content">
              <div className="alchi-lm-message-avatar">
                {message.role === 'user' ? '👤' : '🤖'}
              </div>
              <div className="alchi-lm-message-body">
                <p className="alchi-lm-message-text">{message.content}</p>
                <span className="alchi-lm-message-time">
                  {formatTimestamp(message.timestamp)}
                </span>
              </div>
            </div>
          </div>
        ))}
        
        {isLoading && (
          <div className="alchi-lm-message alchi-lm-message--assistant">
            <div className="alchi-lm-message-content">
              <div className="alchi-lm-message-avatar">🤖</div>
              <div className="alchi-lm-message-body">
                <div className="alchi-lm-typing-indicator">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>
            </div>
          </div>
        )}
        
        <div ref={messagesEndRef} />
      </div>

      {error && (
        <div className="alchi-lm-error">
          <span className="alchi-lm-error-icon">⚠️</span>
          {error}
        </div>
      )}

      <div className="alchi-lm-input-container">
        <textarea
          ref={inputRef}
          className="alchi-lm-input"
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          disabled={isLoading}
          rows={1}
        />
        <button
          className="alchi-lm-send-btn"
          onClick={sendMessage}
          disabled={!inputValue.trim() || isLoading}
          title="Send message"
        >
          {isLoading ? '...' : 'Send'}
        </button>
      </div>

      <style>{`
        .alchi-lm-container {
          display: flex;
          flex-direction: column;
          height: 100%;
          max-height: 100vh;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        .alchi-lm-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1rem 1.5rem;
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(10px);
          border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alchi-lm-title {
          margin: 0;
          font-size: 1.5rem;
          color: white;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }

        .alchi-lm-icon {
          font-size: 1.75rem;
        }

        .alchi-lm-clear-btn {
          padding: 0.5rem 1rem;
          background: rgba(255, 255, 255, 0.2);
          border: 1px solid rgba(255, 255, 255, 0.3);
          border-radius: 0.5rem;
          color: white;
          cursor: pointer;
          transition: all 0.2s ease;
        }

        .alchi-lm-clear-btn:hover {
          background: rgba(255, 255, 255, 0.3);
        }

        .alchi-lm-messages {
          flex: 1;
          overflow-y: auto;
          padding: 1rem;
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }

        .alchi-lm-message {
          display: flex;
          animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .alchi-lm-message--user {
          justify-content: flex-end;
        }

        .alchi-lm-message--assistant {
          justify-content: flex-start;
        }

        .alchi-lm-message-content {
          display: flex;
          gap: 0.75rem;
          max-width: 80%;
        }

        .alchi-lm-message--user .alchi-lm-message-content {
          flex-direction: row-reverse;
        }

        .alchi-lm-message-avatar {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.9);
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.25rem;
          flex-shrink: 0;
        }

        .alchi-lm-message-body {
          background: rgba(255, 255, 255, 0.95);
          padding: 0.75rem 1rem;
          border-radius: 1rem;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .alchi-lm-message--user .alchi-lm-message-body {
          background: rgba(102, 126, 234, 0.9);
          color: white;
        }

        .alchi-lm-message-text {
          margin: 0 0 0.25rem 0;
          line-height: 1.5;
          white-space: pre-wrap;
        }

        .alchi-lm-message-time {
          font-size: 0.75rem;
          opacity: 0.7;
        }

        .alchi-lm-typing-indicator {
          display: flex;
          gap: 4px;
          padding: 0.5rem 0;
        }

        .alchi-lm-typing-indicator span {
          width: 8px;
          height: 8px;
          background: #667eea;
          border-radius: 50%;
          animation: bounce 1.4s infinite ease-in-out;
        }

        .alchi-lm-typing-indicator span:nth-child(1) {
          animation-delay: -0.32s;
        }

        .alchi-lm-typing-indicator span:nth-child(2) {
          animation-delay: -0.16s;
        }

        @keyframes bounce {
          0%, 80%, 100% {
            transform: scale(0);
          }
          40% {
            transform: scale(1);
          }
        }

        .alchi-lm-error {
          margin: 0 1rem;
          padding: 0.75rem 1rem;
          background: rgba(255, 107, 107, 0.9);
          color: white;
          border-radius: 0.5rem;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }

        .alchi-lm-input-container {
          display: flex;
          gap: 0.75rem;
          padding: 1rem;
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(10px);
          border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alchi-lm-input {
          flex: 1;
          padding: 0.75rem 1rem;
          border: none;
          border-radius: 1.5rem;
          font-size: 1rem;
          resize: none;
          outline: none;
          background: rgba(255, 255, 255, 0.95);
          min-height: 44px;
          max-height: 120px;
        }

        .alchi-lm-input:focus {
          box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.5);
        }

        .alchi-lm-input::placeholder {
          color: #999;
        }

        .alchi-lm-send-btn {
          padding: 0.75rem 1.5rem;
          background: rgba(255, 255, 255, 0.95);
          border: none;
          border-radius: 1.5rem;
          font-size: 1rem;
          font-weight: 600;
          color: #667eea;
          cursor: pointer;
          transition: all 0.2s ease;
        }

        .alchi-lm-send-btn:hover:not(:disabled) {
          background: white;
          transform: scale(1.05);
        }

        .alchi-lm-send-btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      `}</style>
    </div>
  );
};

export default AlchiLMPage;
