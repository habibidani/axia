<?php

namespace App\Livewire;

use App\Models\AgentSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AiChat extends Component
{
    public $message = '';
    public $messages = [];
    public $sessionId = null;
    public $isLoading = false;
    public $error = null;

    public function mount()
    {
        // Initialize session if needed
        $existingSession = AgentSession::where('user_id', auth()->id())
            ->active()
            ->latest()
            ->first();

        if ($existingSession) {
            $this->sessionId = $existingSession->session_id;
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = $this->message;
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->format('H:i'),
        ];

        $this->message = '';
        $this->isLoading = true;
        $this->error = null;

        try {
            // Use internal Laravel API instead of direct webhook call
            $token = auth()->user()->tokens()->first()?->plainTextToken 
                ?? auth()->user()->createToken('livewire-chat')->plainTextToken;

            $response = Http::withToken($token)
                ->timeout(120)
                ->post(url('/api/chat/start'), [
                    'message' => $userMessage,
                    'mode' => 'chat',
                ]);

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
                            $aiResponse .= $data['content'];
                        }
                    }
                }

                if ($aiResponse) {
                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => $aiResponse,
                        'timestamp' => now()->format('H:i'),
                    ];
                } elseif (!$this->error) {
                    $this->error = 'Keine Antwort vom AI-Service erhalten.';
                }
            } else {
                $this->error = 'API-Anfrage fehlgeschlagen: ' . $response->status();
                Log::error('AiChat API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
                $errorBody = $webhookResponse->body();
                Log::error('n8n webhook error', [
                    'status' => $webhookResponse->status(),
                    'body' => $errorBody,
                }
            } else {
                $this->error = 'API-Anfrage fehlgeschlagen: ' . $response->status();
                Log::error('AiChat API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            $this->error = 'Ein Fehler ist aufgetreten: ' . $e->getMessage();
            
            Log::error('AI Chat error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->isLoading = false;
            
            if ($this->error) {
                $this->messages[] = [
                    'role' => 'error',
                    'content' => $this->error,
                    'timestamp' => now()->format('H:i'),
                ];
            }
        }
    }

    public function clearChat()
    {
        $this->messages = [];
        $this->sessionId = null;
        $this->message = '';
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.ai-chat');
    }
}
