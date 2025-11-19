<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Str;
use Livewire\Component;

class ApiTokens extends Component
{
    public $tokens = [];
    public $newTokenName = '';
    public $newlyCreatedToken = null;
    public $showCreateForm = false;

    protected $rules = [
        'newTokenName' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->loadTokens();
    }

    public function loadTokens()
    {
        $this->tokens = auth()->user()->tokens()->get();
    }

    public function createToken()
    {
        $this->validate();

        $token = auth()->user()->createToken($this->newTokenName);

        $this->newlyCreatedToken = $token->plainTextToken;
        $this->newTokenName = '';
        $this->loadTokens();

        session()->flash('message', 'API Token created successfully. Make sure to copy it now, you won\'t be able to see it again!');
    }

    public function deleteToken($tokenId)
    {
        auth()->user()->tokens()->where('id', $tokenId)->delete();
        
        $this->loadTokens();
        
        session()->flash('message', 'API Token deleted successfully.');
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->newlyCreatedToken = null;
    }

    public function render()
    {
        return view('livewire.settings.api-tokens');
    }
}
