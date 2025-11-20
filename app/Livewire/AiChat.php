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

        try {
            // Create or use existing session
            if (!$this->sessionId) {
                $session = AgentSession::create([
                    'user_id' => auth()->id(),
                    'mode' => 'chat',
                    'meta' => [
                        'user_email' => auth()->user()->email,
                        'user_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                        'company_id' => auth()->user()->company_id,
                    ],
                ]);
                $this->sessionId = $session->session_id;
            }

            // Call n8n webhook directly with GET request
            $webhookUrl = config('services.n8n.agent_webhook_url');
            $queryParams = http_build_query([
                'sessionId' => $this->sessionId,
                'chatInput' => $userMessage,
                'userId' => auth()->id(),
                'companyId' => auth()->user()->company_id,
                'mode' => 'chat',
            ]);

            $fullUrl = $webhookUrl . '?' . $queryParams;

            Log::channel('stack')->info('Livewire calling n8n webhook', [
                'user_id' => auth()->id(),
                'session_id' => $this->sessionId,
                'message_preview' => substr($userMessage, 0, 50),
            ]);

            $webhookResponse = Http::timeout(60)->get($fullUrl);

            if ($webhookResponse->successful()) {
                $data = $webhookResponse->json();
                
                // n8n returns {type, content, metadata}
                if (isset($data['type']) && $data['type'] === 'error') {
                    $this->messages[] = [
                        'role' => 'error',
                        'content' => $data['content'] ?? 'Unbekannter Fehler vom AI-Service.',
                        'timestamp' => now()->format('H:i'),
                    ];
                } else {
                    $aiResponse = $data['content'] ?? $webhookResponse->body();
                    
                    $this->messages[] = [
                        'role' => 'assistant',
                        'content' => $aiResponse,
                        'timestamp' => now()->format('H:i'),
                    ];
                }
            } else {
                $errorBody = $webhookResponse->body();
                Log::error('n8n webhook error', [
                    'status' => $webhookResponse->status(),
                    'body' => $errorBody,
                ]);

                $this->messages[] = [
                    'role' => 'error',
                    'content' => 'Fehler bei der Verbindung zum AI-Service (Status: ' . $webhookResponse->status() . ')',
                    'timestamp' => now()->format('H:i'),
                ];
            }

        } catch (\Exception $e) {
            Log::error('AI Chat error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->messages[] = [
                'role' => 'error',
                'content' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage(),
                'timestamp' => now()->format('H:i'),
            ];
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearChat()
    {
        $this->messages = [];
        $this->sessionId = null;
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.ai-chat');
    }
}
