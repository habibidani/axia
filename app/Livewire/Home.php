<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\MissingTodo;
use App\Models\Run;
use App\Models\Todo;
use App\Models\TodoEvaluation;
use App\Services\OpenAiAnalysisService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Home extends Component
{
    use WithFileUploads;

    public $todoText = '';
    public $csvFile;
    public $showCsvUpload = false;

    public function mount()
    {
        // Create company if user doesn't have one
        if (!auth()->user()->is_guest && !auth()->user()->company) {
            Company::create([
                'owner_user_id' => auth()->id(),
            ]);
        }
    }

    public function analyzeTodos()
    {
        $this->validate([
            'todoText' => 'required|string',
        ]);

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
            $topKpi = $company?->topKpi();

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

            // Analyze with OpenAI
            $analysisService = new OpenAiAnalysisService();
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

            return redirect()->route('results.show', $run);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Analysis failed: ' . $e->getMessage());
        }
    }

    public function uploadCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

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
            $topKpi = $company?->topKpi();

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

            // Analyze with OpenAI
            $analysisService = new OpenAiAnalysisService();
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

            return redirect()->route('results.show', $run);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'CSV upload failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $company = $user->company;
        $topKpi = $company?->topKpi();
        $lastRun = $company ? $company->runs()->latest()->first() : null;

        return view('livewire.home', [
            'company' => $company,
            'topKpi' => $topKpi,
            'lastRun' => $lastRun,
        ])->layout('components.layouts.app');
    }
}

