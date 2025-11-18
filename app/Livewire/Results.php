<?php

namespace App\Livewire;

use App\Models\Run;
use Livewire\Component;

class Results extends Component
{
    public Run $run;
    public $expandedTasks = [];

    public function mount(Run $run)
    {
        $this->run = $run->load([
            'company',
            'snapshotTopKpi',
            'todos.evaluation.primaryGoal',
            'todos.evaluation.primaryKpi',
            'missingTodos.goal',
            'missingTodos.kpi',
        ]);
    }

    public function toggleTask($todoId)
    {
        if (in_array($todoId, $this->expandedTasks)) {
            $this->expandedTasks = array_filter($this->expandedTasks, fn($id) => $id !== $todoId);
        } else {
            $this->expandedTasks[] = $todoId;
        }
    }

    public function exportCsv()
    {
        $filename = 'axia-focus-report-' . $this->run->created_at->format('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        
        // Header row
        fputcsv($handle, [
            'Task',
            'Score',
            'Color',
            'Priority',
            'Action',
            'Reasoning',
            'Linked Goal',
            'Linked KPI',
            'Delegation Role',
        ]);

        // Task rows
        foreach ($this->run->todos as $todo) {
            $evaluation = $todo->evaluation;
            
            if (!$evaluation) continue;

            fputcsv($handle, [
                $todo->normalized_title,
                $evaluation->score,
                $evaluation->color,
                $evaluation->priority_recommendation ?? '',
                $evaluation->action_recommendation ?? '',
                $evaluation->reasoning,
                $evaluation->primaryGoal?->title ?? '',
                $evaluation->primaryKpi?->name ?? '',
                $evaluation->delegation_target_role ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(
            fn() => print($csv),
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }

    public function render()
    {
        $sortedTodos = $this->run->todos->sortByDesc(fn($todo) => $todo->evaluation?->score ?? 0);

        return view('livewire.results', [
            'sortedTodos' => $sortedTodos,
        ])->layout('components.layouts.app');
    }
}

