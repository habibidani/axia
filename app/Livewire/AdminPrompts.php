<?php

namespace App\Livewire;

use App\Models\SystemPrompt;
use Livewire\Component;

class AdminPrompts extends Component
{
    public $activeTab = 'todo_analysis';
    public $editingId = null;
    public $showModal = false;

    // Form fields
    public $type = 'todo_analysis';
    public $version = 'v1.0';
    public $system_message = '';
    public $user_prompt_template = '';
    public $temperature = 0.7;
    public $is_active = false;

    public function mount()
    {
        // Restrict to authenticated non-guest users with company
        if (!auth()->check() || auth()->user()->is_guest || !auth()->user()->company) {
            abort(403, 'Admin access restricted to company owners only.');
        }
    }

    public function createPrompt()
    {
        $this->reset(['editingId', 'type', 'version', 'system_message', 'user_prompt_template', 'temperature', 'is_active']);
        $this->type = $this->activeTab;
        $this->showModal = true;
    }

    public function editPrompt($id)
    {
        $prompt = SystemPrompt::findOrFail($id);

        $this->editingId = $id;
        $this->type = $prompt->type;
        $this->version = $prompt->version;
        $this->system_message = $prompt->system_message;
        $this->user_prompt_template = $prompt->user_prompt_template;
        $this->temperature = $prompt->temperature;
        $this->is_active = $prompt->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:todo_analysis,company_extraction,goals_extraction',
            'version' => 'required|string|max:50',
            'system_message' => 'required|string',
            'user_prompt_template' => 'required|string',
            'temperature' => 'required|numeric|min:0|max:1',
            'is_active' => 'boolean',
        ]);

        if ($this->editingId) {
            $prompt = SystemPrompt::findOrFail($this->editingId);

            // Prevent editing system default prompts - force clone instead
            if ($prompt->is_system_default) {
                session()->flash('error', 'Cannot edit system default prompts. Clone to customize.');
                $this->showModal = false;
                return;
            }

            $prompt->update([
                'type' => $this->type,
                'version' => $this->version,
                'system_message' => $this->system_message,
                'user_prompt_template' => $this->user_prompt_template,
                'temperature' => $this->temperature,
                'is_active' => $this->is_active,
            ]);
        } else {
            SystemPrompt::create([
                'type' => $this->type,
                'version' => $this->version,
                'system_message' => $this->system_message,
                'user_prompt_template' => $this->user_prompt_template,
                'temperature' => $this->temperature,
                'is_active' => $this->is_active,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'type', 'version', 'system_message', 'user_prompt_template', 'temperature', 'is_active']);

        session()->flash('success', 'Prompt saved successfully!');
    }

    public function toggleActive($id)
    {
        $prompt = SystemPrompt::findOrFail($id);

        // If activating, deactivate all others of same type
        if (!$prompt->is_active) {
            SystemPrompt::where('type', $prompt->type)->update(['is_active' => false]);
        }

        $prompt->update(['is_active' => !$prompt->is_active]);
    }

    public function clonePrompt($id)
    {
        $original = SystemPrompt::findOrFail($id);

        $this->type = $original->type;
        $this->version = $original->version . '-copy';
        $this->system_message = $original->system_message;
        $this->user_prompt_template = $original->user_prompt_template;
        $this->temperature = $original->temperature;
        $this->is_active = false;

        $this->showModal = true;
    }

    public function deletePrompt($id)
    {
        $prompt = SystemPrompt::findOrFail($id);

        // Prevent deletion of system default prompts
        if ($prompt->is_system_default) {
            session()->flash('error', 'Cannot delete system default prompts. Clone and customize instead.');
            return;
        }

        $prompt->delete();
        session()->flash('success', 'Prompt deleted!');
    }

    public function render()
    {
        $prompts = SystemPrompt::where('type', $this->activeTab)
            ->latest()
            ->get();

        return view('livewire.admin-prompts', [
            'prompts' => $prompts,
        ])->layout('components.layouts.app');
    }
}

