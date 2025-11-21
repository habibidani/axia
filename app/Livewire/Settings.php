<?php

namespace App\Livewire;

use App\Models\WebhookPreset;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Settings extends Component
{
    public string $newPresetName = '';
    public string $newPresetUrl = '';
    public string $newPresetDescription = '';
    
    public function mount()
    {
        // Initialize with default webhook if no presets exist
        $user = auth()->user();
        if ($user->webhookPresets()->count() === 0) {
            $defaultWebhook = 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d';
            
            WebhookPreset::create([
                'user_id' => $user->id,
                'name' => 'Standard AI',
                'webhook_url' => $defaultWebhook,
                'description' => 'Standard n8n AI Workflow',
                'is_active' => true,
                'is_default' => true,
            ]);
            
            // Update user's webhook URL if not set
            if (!$user->n8n_webhook_url) {
                $user->update(['n8n_webhook_url' => $defaultWebhook]);
            }
        }
    }

    public function addWebhookPreset()
    {
        Log::info('addWebhookPreset called', [
            'name' => $this->newPresetName,
            'url' => $this->newPresetUrl,
            'description' => $this->newPresetDescription,
        ]);

        $this->validate([
            'newPresetName' => 'required|string|max:100',
            'newPresetUrl' => 'required|url|max:255',
            'newPresetDescription' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        
        Log::info('Creating preset for user', ['user_id' => $user->id]);
        
        $preset = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => $this->newPresetName,
            'webhook_url' => $this->newPresetUrl,
            'description' => $this->newPresetDescription,
            'is_active' => false,
        ]);

        Log::info('Preset created', ['preset_id' => $preset->id]);

        // Reset form
        $this->reset(['newPresetName', 'newPresetUrl', 'newPresetDescription']);
        
        session()->flash('success', 'Webhook gespeichert!');
    }

    public function activatePreset($presetId)
    {
        Log::info('activatePreset called', ['preset_id' => $presetId]);
        
        $preset = WebhookPreset::where('user_id', auth()->id())
            ->findOrFail($presetId);
        
        Log::info('Found preset', ['preset' => $preset->toArray()]);
        
        $preset->activate();
        
        Log::info('Preset activated');
        
        session()->flash('success', 'Webhook "' . $preset->name . '" aktiviert!');
    }

    public function deletePreset($presetId)
    {
        $preset = WebhookPreset::where('user_id', auth()->id())
            ->findOrFail($presetId);
        
        if ($preset->is_default) {
            session()->flash('error', 'Standard-Webhook kann nicht gelöscht werden!');
            return;
        }
        
        $preset->delete();
        
        session()->flash('success', 'Webhook gelöscht!');
    }

    public function testWebhook($presetId = null)
    {
        $webhookUrl = null;
        
        if ($presetId) {
            $preset = WebhookPreset::where('user_id', auth()->id())->findOrFail($presetId);
            $webhookUrl = $preset->webhook_url;
        } elseif (!empty($this->newPresetUrl)) {
            $webhookUrl = $this->newPresetUrl;
        }

        if (empty($webhookUrl)) {
            session()->flash('error', 'Bitte gib eine Webhook URL ein.');
            return;
        }

        try {
            $response = Http::timeout(10)->post($webhookUrl, [
                'task' => 'ping',
                'system_message' => 'Test',
                'user_prompt' => 'Respond with "pong"',
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                session()->flash('success', 'Webhook erfolgreich getestet! Status: ' . $response->status());
            } else {
                session()->flash('error', 'Webhook Fehler: ' . $response->status());
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Webhook Test fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $presets = auth()->user()->webhookPresets()->orderBy('is_active', 'desc')->orderBy('created_at', 'asc')->get();
        
        return view('livewire.settings', [
            'presets' => $presets,
        ]);
    }
}
