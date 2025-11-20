<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Settings extends Component
{
    public string $webhookUrl = '';
    public array $webhookConfig = [];
    
    public function mount()
    {
        $user = auth()->user();
        $this->webhookUrl = $user->n8n_webhook_url ?? '';
        $this->webhookConfig = $user->webhook_config ?? [];
    }

    public function saveWebhookUrl()
    {
        $this->validate([
            'webhookUrl' => 'required|url|max:255',
        ]);

        $user = auth()->user();
        $user->n8n_webhook_url = $this->webhookUrl;
        $user->save();

        session()->flash('success', 'Webhook URL gespeichert!');
    }

    public function testWebhook()
    {
        if (empty($this->webhookUrl)) {
            session()->flash('error', 'Bitte gib eine Webhook URL ein.');
            return;
        }

        try {
            $response = Http::timeout(10)->post($this->webhookUrl, [
                'task' => 'ping',
                'system_message' => 'Test',
                'user_prompt' => 'Respond with "pong"',
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                session()->flash('success', 'Webhook erfolgreich getestet! Status: ' . $response->status());
            } else {
                session()->flash('error', 'Webhook Fehler: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Webhook Test fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function useDefaultWebhook()
    {
        $this->webhookUrl = config('services.n8n.ai_analysis_webhook_url', 
            'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d');
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
