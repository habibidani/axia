<?php

namespace App\Livewire;

use App\Models\Run;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AllAnalyses extends Component
{
    public function render()
    {
        $runs = Run::where('user_id', Auth::id())
            ->with(['todos', 'company'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.all-analyses', [
            'runs' => $runs,
        ])->layout('components.layouts.app');
    }
}

