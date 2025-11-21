<?php

namespace App\Livewire;

use App\Models\Run;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Results extends Component
{
    public Run $run;
    public $expandedTasks = [];
    public $chartUrl = null;
    public $chartLoading = false;

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

    public function generateChart()
    {
        $this->chartLoading = true;
        $this->chartUrl = null;

        try {
            // Force fresh user data from database
            $user = auth()->user()->fresh();
            
            // Use dedicated chart webhook or fall back to main webhook
            $webhookUrl = $user->chart_webhook_url 
                ?? $user->n8n_webhook_url 
                ?? config('services.n8n.chart_webhook_url');
            
            if (!$webhookUrl) {
                session()->flash('error', 'No chart webhook URL configured');
                $this->chartLoading = false;
                return;
            }
            
            // Prepare top 10 tasks for chart
            $topTasks = $this->run->todos
                ->sortByDesc(fn($todo) => $todo->evaluation?->score ?? 0)
                ->take(10)
                ->map(function($todo) {
                    return [
                        'task' => $todo->normalized_title,
                        'score' => $todo->evaluation?->score ?? 0,
                    ];
                })
                ->values()
                ->toArray();
            
            // Create concise prompt for AI agent
            $prompt = "Create a funnel chart with these tasks: " . json_encode($topTasks);
            
            $requestData = [
                'guardrailsInput' => $prompt,
                'user_id' => $user->id,
            ];

            Log::info('Calling chart webhook', ['url' => $webhookUrl, 'user_id' => $user->id]);

            try {
                // Use SAME approach as WebhookAiService
                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($webhookUrl, $requestData);

                if ($response->failed()) {
                    Log::error('Chart webhook failed', ['status' => $response->status()]);
                    session()->flash('error', 'Chart generation failed: ' . $response->status());
                    return;
                }

                $body = $response->body();
                $contentType = $response->header('Content-Type');

                Log::info('Chart webhook response', [
                    'status' => $response->status(),
                    'body_length' => strlen($body),
                    'content_type' => $contentType,
                ]);

                // Check if response is SSE stream (same as WebhookAiService)
                if (str_contains($contentType, 'application/json') && str_contains($body, '{"type":"item"')) {
                    // Parse SSE events and collect content
                    $content = '';
                    $lines = explode("\n", $body);
                    
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        $event = json_decode($line, true);
                        if ($event && isset($event['type']) && $event['type'] === 'item' && isset($event['content'])) {
                            $content .= $event['content'];
                        }
                    }

                    Log::info('SSE content collected', ['length' => strlen($content), 'preview' => substr($content, 0, 200)]);

                    // Extract URL from collected content
                    if (preg_match('/https:\/\/[a-zA-Z0-9._\-\/]+/', $content, $matches)) {
                        $this->chartUrl = $matches[0];
                        Log::info('Chart URL extracted', ['url' => $this->chartUrl]);
                    } else {
                        Log::warning('No URL found in SSE content', ['content' => substr($content, 0, 500)]);
                        session()->flash('error', 'Chart URL not found in response');
                    }
                } else {
                    // Try direct extraction from body
                    if (preg_match('/https:\/\/[a-zA-Z0-9._\-\/]+/', $body, $matches)) {
                        $this->chartUrl = $matches[0];
                        Log::info('Chart URL extracted from body', ['url' => $this->chartUrl]);
                    } else {
                        Log::warning('No URL found', ['body_sample' => substr($body, 0, 500)]);
                        session()->flash('error', 'Chart URL not found in response');
                    }
                }
            } catch (\Exception $e) {
                Log::error('Chart webhook exception', ['error' => $e->getMessage()]);
                session()->flash('error', 'Chart generation failed: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Chart generation failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Chart generation failed: ' . $e->getMessage());
        } finally {
            $this->chartLoading = false;
        }
    }

    public function render()
    {
        $sortedTodos = $this->run->todos->sortByDesc(fn($todo) => $todo->evaluation?->score ?? 0);

        return view('livewire.results', [
            'sortedTodos' => $sortedTodos,
        ])->layout('components.layouts.app');
    }
}

