<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\MissingTodo;
use App\Models\Run;
use App\Models\Todo;
use App\Models\TodoEvaluation;
use App\Services\ExampleContentService;
use App\Services\WebhookAiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Home extends Component
{
    use WithFileUploads;

    public $chatMessages = [];
    public $chatInput = '';
    public $isSending = false;

    public function mount()
    {
        // Create company if user doesn't have one
        if (!auth()->user()->is_guest && !auth()->user()->company) {
            Company::create([
                'owner_user_id' => auth()->id(),
            ]);
        }

        // Initialize with welcome message
        $this->chatMessages = [
            [
                'role' => 'assistant',
                'content' => 'Hi! I\'m your AI assistant. How can I help you today?',
                'timestamp' => now()->format('H:i'),
            ],
        ];
    }

    public function sendMessage()
    {
        if (empty(trim($this->chatInput))) {
            return;
        }

        $this->isSending = true;

        // Add user message
        $this->chatMessages[] = [
            'role' => 'user',
            'content' => $this->chatInput,
            'timestamp' => now()->format('H:i'),
        ];

        $userMessage = $this->chatInput;
        $this->chatInput = '';

        try {
            $user = auth()->user();
            $webhookUrl = $user->n8n_webhook_url ?? 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d';

            // Send to webhook
            $service = new WebhookAiService();
            $response = $service->sendChatMessage($webhookUrl, $userMessage, $user->company);

            // Add AI response
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => $response['message'] ?? 'I received your message but couldn\'t generate a response.',
                'timestamp' => now()->format('H:i'),
            ];

        } catch (\Exception $e) {
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => 'Sorry, I encountered an error: ' . $e->getMessage(),
                'timestamp' => now()->format('H:i'),
            ];
        }

        $this->isSending = false;
    }

    public function analyzeTodos()
    {
        $this->validate([
            'todoText' => 'required|string',
        ]);

        $this->analyzing = true;

        $lines = array_filter(
            array_map('trim', explode("\n", $this->todoText)),
            fn($line) => !empty($line)
        );

        if (empty($lines)) {
            session()->flash('error', 'Please enter at least one task.');
            return;
        }

        try {
            DB::beginTransaction();

            $company = auth()->user()->company;
            $topKpi = $company?->top_kpi;

            // Create run
            $run = Run::create([
                'company_id' => $company?->id,
                'user_id' => auth()->id(),
                'snapshot_top_kpi_id' => $topKpi?->id,
            ]);

            // Create todos
            $todos = collect();
            foreach ($lines as $index => $line) {
                $todo = Todo::create([
                    'run_id' => $run->id,
                    'raw_input' => $line,
                    'normalized_title' => $line,
                    'source' => 'paste',
                    'position' => $index,
                ]);
                $todos->push($todo);
            }

            // Analyze with AI via webhook
            $analysisService = new WebhookAiService();
            $result = $analysisService->analyzeTodos($run, $todos, $company);

            // Store overall results
            $run->update([
                'overall_score' => $result['overall_score'] ?? null,
                'summary_text' => $result['summary_text'] ?? null,
            ]);

            // Store evaluations
            if (isset($result['evaluations'])) {
                foreach ($result['evaluations'] as $evaluation) {
                    $todo = $todos[$evaluation['task_index']] ?? null;
                    if (!$todo) continue;

                    // Find goal and KPI by title/name
                    $goal = null;
                    $kpi = null;

                    if ($company && isset($evaluation['goal_title'])) {
                        $goal = $company->goals()->where('title', $evaluation['goal_title'])->first();
                    }

                    if ($goal && isset($evaluation['kpi_name'])) {
                        $kpi = $goal->kpis()->where('name', $evaluation['kpi_name'])->first();
                    }

                    TodoEvaluation::create([
                        'run_id' => $run->id,
                        'todo_id' => $todo->id,
                        'color' => $evaluation['color'],
                        'score' => $evaluation['score'],
                        'reasoning' => $evaluation['reasoning'],
                        'priority_recommendation' => $evaluation['priority_recommendation'] ?? null,
                        'action_recommendation' => $evaluation['action_recommendation'] ?? null,
                        'delegation_target_role' => $evaluation['delegation_target_role'] ?? null,
                        'primary_goal_id' => $goal?->id,
                        'primary_kpi_id' => $kpi?->id,
                    ]);
                }
            }

            // Store missing todos
            if (isset($result['missing_todos'])) {
                foreach ($result['missing_todos'] as $missing) {
                    $goal = null;
                    $kpi = null;

                    if ($company && isset($missing['goal_title'])) {
                        $goal = $company->goals()->where('title', $missing['goal_title'])->first();
                    }

                    if ($goal && isset($missing['kpi_name'])) {
                        $kpi = $goal->kpis()->where('name', $missing['kpi_name'])->first();
                    }

                    MissingTodo::create([
                        'run_id' => $run->id,
                        'goal_id' => $goal?->id,
                        'kpi_id' => $kpi?->id,
                        'title' => $missing['title'],
                        'description' => $missing['description'] ?? null,
                        'category' => $missing['category'] ?? null,
                        'impact_score' => $missing['impact_score'] ?? null,
                        'suggested_owner_role' => $missing['suggested_owner_role'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $this->analyzing = false;
            return redirect()->route('results.show', $run);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->analyzing = false;
            session()->flash('error', 'Analysis failed: ' . $e->getMessage());
        }
    }

    public function uploadCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $this->analyzing = true;

        try {
            $path = $this->csvFile->getRealPath();
            $file = fopen($path, 'r');

            $header = fgetcsv($file);
            $taskIndex = array_search('Task', $header);
            $ownerIndex = array_search('Owner', $header);
            $dueDateIndex = array_search('Due Date', $header);

            if ($taskIndex === false) {
                session()->flash('error', 'CSV must have a "Task" column.');
                return;
            }

            $tasks = [];
            while (($row = fgetcsv($file)) !== false) {
                if (isset($row[$taskIndex]) && !empty($row[$taskIndex])) {
                    $tasks[] = [
                        'task' => $row[$taskIndex],
                        'owner' => $ownerIndex !== false && isset($row[$ownerIndex]) ? $row[$ownerIndex] : null,
                        'due_date' => $dueDateIndex !== false && isset($row[$dueDateIndex]) ? $row[$dueDateIndex] : null,
                    ];
                }
            }
            fclose($file);

            if (empty($tasks)) {
                session()->flash('error', 'No valid tasks found in CSV.');
                return;
            }

            DB::beginTransaction();

            $company = auth()->user()->company;
            $topKpi = $company?->top_kpi;

            // Create run
            $run = Run::create([
                'company_id' => $company?->id,
                'user_id' => auth()->id(),
                'snapshot_top_kpi_id' => $topKpi?->id,
            ]);

            // Create todos
            $todos = collect();
            foreach ($tasks as $index => $taskData) {
                $todo = Todo::create([
                    'run_id' => $run->id,
                    'raw_input' => $taskData['task'],
                    'normalized_title' => $taskData['task'],
                    'owner' => $taskData['owner'],
                    'due_date' => $taskData['due_date'] ? date('Y-m-d', strtotime($taskData['due_date'])) : null,
                    'source' => 'csv',
                    'position' => $index,
                ]);
                $todos->push($todo);
            }

            // Analyze with AI via webhook
            $analysisService = new WebhookAiService();
            $result = $analysisService->analyzeTodos($run, $todos, $company);

            // Store results (same as analyzeTodos method)
            $run->update([
                'overall_score' => $result['overall_score'] ?? null,
                'summary_text' => $result['summary_text'] ?? null,
            ]);

            // Store evaluations
            if (isset($result['evaluations'])) {
                foreach ($result['evaluations'] as $evaluation) {
                    $todo = $todos[$evaluation['task_index']] ?? null;
                    if (!$todo) continue;

                    $goal = null;
                    $kpi = null;

                    if ($company && isset($evaluation['goal_title'])) {
                        $goal = $company->goals()->where('title', $evaluation['goal_title'])->first();
                    }

                    if ($goal && isset($evaluation['kpi_name'])) {
                        $kpi = $goal->kpis()->where('name', $evaluation['kpi_name'])->first();
                    }

                    TodoEvaluation::create([
                        'run_id' => $run->id,
                        'todo_id' => $todo->id,
                        'color' => $evaluation['color'],
                        'score' => $evaluation['score'],
                        'reasoning' => $evaluation['reasoning'],
                        'priority_recommendation' => $evaluation['priority_recommendation'] ?? null,
                        'action_recommendation' => $evaluation['action_recommendation'] ?? null,
                        'delegation_target_role' => $evaluation['delegation_target_role'] ?? null,
                        'primary_goal_id' => $goal?->id,
                        'primary_kpi_id' => $kpi?->id,
                    ]);
                }
            }

            // Store missing todos
            if (isset($result['missing_todos'])) {
                foreach ($result['missing_todos'] as $missing) {
                    $goal = null;
                    $kpi = null;

                    if ($company && isset($missing['goal_title'])) {
                        $goal = $company->goals()->where('title', $missing['goal_title'])->first();
                    }

                    if ($goal && isset($missing['kpi_name'])) {
                        $kpi = $goal->kpis()->where('name', $missing['kpi_name'])->first();
                    }

                    MissingTodo::create([
                        'run_id' => $run->id,
                        'goal_id' => $goal?->id,
                        'kpi_id' => $kpi?->id,
                        'title' => $missing['title'],
                        'description' => $missing['description'] ?? null,
                        'category' => $missing['category'] ?? null,
                        'impact_score' => $missing['impact_score'] ?? null,
                        'suggested_owner_role' => $missing['suggested_owner_role'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $this->analyzing = false;
            return redirect()->route('results.show', $run);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->analyzing = false;
            session()->flash('error', 'CSV upload failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $company = $user->company;
        $topKpi = $company?->top_kpi;
        $topGoal = $company ? $company->goals()->orderBy('priority')->first() : null;
        $lastRun = $company ? $company->runs()->latest()->first() : null;

        // Chart Data: Score History (last 10 runs)
        $scoreHistory = [];
        if ($company) {
            $recentRuns = $company->runs()->latest()->take(10)->get()->reverse();
            foreach ($recentRuns as $run) {
                $scoreHistory[] = [
                    'date' => $run->created_at->format('M d'),
                    'score' => $run->overall_score ?? 0
                ];
            }
        }

        // Chart Data: Todo Distribution (from last run)
        $todoDistribution = ['high' => 0, 'medium' => 0, 'low' => 0];
        if ($lastRun) {
            foreach ($lastRun->todos as $todo) {
                $score = $todo->evaluation?->score ?? $todo->final_score ?? 0;
                if ($score >= 80) {
                    $todoDistribution['high']++;
                } elseif ($score >= 50) {
                    $todoDistribution['medium']++;
                } else {
                    $todoDistribution['low']++;
                }
            }
        }

        // Build calendar events array
        $events = [];

        if ($company) {
            // Analysis events from runs
            foreach ($company->runs->take(20) as $run) {
                $events[] = [
                    'title' => '📊 Analysis: ' . $run->overall_score . '/100',
                    'start' => $run->created_at->format('Y-m-d'),
                    'backgroundColor' => $run->overall_score >= 80 ? '#10b981' : ($run->overall_score >= 60 ? '#f59e0b' : '#ef4444'),
                    'borderColor' => $run->overall_score >= 80 ? '#059669' : ($run->overall_score >= 60 ? '#d97706' : '#dc2626'),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'analysis',
                        'score' => $run->overall_score,
                        'todosCount' => $run->todos->count()
                    ]
                ];
            }
        }

        if ($topGoal) {
            // KPI events
            foreach ($topGoal->kpis as $kpi) {
                $events[] = [
                    'title' => '🎯 ' . \Str::limit($kpi->name, 20),
                    'start' => now()->addDays(30)->format('Y-m-d'),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#2563eb',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'kpi',
                        'name' => $kpi->name,
                        'current' => $kpi->current_value,
                        'target' => $kpi->target_value,
                        'unit' => $kpi->unit
                    ]
                ];
            }
        }

        if ($company && $company->goals) {
            // Goal events
            foreach ($company->goals->take(5) as $goal) {
                $events[] = [
                    'title' => '🚀 ' . \Str::limit($goal->name, 25),
                    'start' => now()->addDays(60)->format('Y-m-d'),
                    'backgroundColor' => '#8b5cf6',
                    'borderColor' => '#7c3aed',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'goal',
                        'name' => $goal->name,
                        'description' => \Str::limit($goal->description ?? '', 100),
                        'priority' => $goal->priority
                    ]
                ];
            }
        }

        return view('livewire.home', [
            'company' => $company,
            'topKpi' => $topKpi,
            'topGoal' => $topGoal,
            'lastRun' => $lastRun,
            'events' => $events,
            'scoreHistory' => $scoreHistory,
            'todoDistribution' => $todoDistribution,
        ])->layout('components.layouts.app');
    }
}

