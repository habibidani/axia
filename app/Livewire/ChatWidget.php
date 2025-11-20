<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ChatWidget extends Component
{
    public array $messages = [];
    public string $userInput = '';
    public bool $isOpen = false;
    public bool $isLoading = false;
    public ?string $error = null;
    public ?string $sessionId = null;

    protected $listeners = ['toggleChat'];

    public function mount()
    {
        // Initialize with welcome message
        $this->messages = [
            [
                'role' => 'assistant',
                'content' => 'Hallo! Ich bin dein AI-Assistent. Wie kann ich dir helfen?',
                'timestamp' => now()->format('H:i'),
            ]
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        if (empty(trim($this->userInput))) {
            return;
        }

        $userMessage = trim($this->userInput);
        
        // Add user message to chat
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->format('H:i'),
        ];

        $this->userInput = '';
        $this->isLoading = true;
        $this->error = null;

        try {
            // Use internal Laravel API
            $token = auth()->user()->tokens()->first()?->plainTextToken 
                ?? auth()->user()->createToken('chat-widget')->plainTextToken;

            $endpoint = $this->sessionId 
                ? url('/api/chat/message')
                : url('/api/chat/start');

            $payload = $this->sessionId
                ? ['session_id' => $this->sessionId, 'message' => $userMessage]
                : ['message' => $userMessage, 'mode' => 'chat'];

            $response = Http::withToken($token)
                ->timeout(120)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                // Parse SSE response
                $body = $response->body();
                $lines = explode("\n", $body);
                $aiResponse = '';

                foreach ($lines as $line) {
                    if (str_starts_with($line, 'data: ')) {
                        $jsonData = substr($line, 6);
                        $data = json_decode($jsonData, true);
                        
                        if (isset($data['type']) && $data['type'] === 'error') {
                            $this->error = $data['error'] ?? 'Unbekannter Fehler vom AI-Service.';
                            break;
                        } elseif (isset($data['content'])) {
                            // New format from ChatController
                            $aiResponse = $data['content'];
                            
                            // Store session ID for follow-up messages
                            if (isset($data['sessionId'])) {
                                $this->sessionId = $data['sessionId'];
                            }
                        }
                    }
                }

                if (!empty($aiResponse)) {
                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => $aiResponse,
                        'timestamp' => now()->format('H:i'),
                    ];
                } elseif (empty($this->error)) {
                    $this->error = 'Keine Antwort vom AI-Service erhalten.';
                }
            } else {
                $errorBody = $response->json();
                $this->error = $errorBody['error'] ?? 'Fehler beim Senden der Nachricht: HTTP ' . $response->status();
            }

        } catch (\Exception $e) {
            $this->error = 'Verbindungsfehler: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearChat()
    {
        $this->messages = [
            [
                'role' => 'assistant',
                'content' => 'Chat wurde zurückgesetzt. Wie kann ich dir helfen?',
                'timestamp' => now()->format('H:i'),
            ]
        ];
        $this->sessionId = null;
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
