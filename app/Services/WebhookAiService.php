<?php

namespace App\Services;

use App\Models\AiLog;
use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\Run;
use App\Models\SystemPrompt;
use App\Services\AiResponseValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WebhookAiService - Routes all AI requests through n8n webhook
 * Replaces direct OpenAI API calls with webhook-based architecture
 */
class WebhookAiService
{
    protected string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.n8n.ai_analysis_webhook_url');
        
        if (empty($this->webhookUrl)) {
            throw new \Exception('AI analysis webhook URL not configured. Set N8N_AI_ANALYSIS_WEBHOOK_URL in .env');
        }
    }

    /**
     * Analyze todos against goals and KPIs via webhook
     */
    public function analyzeTodos(Run $run, Collection $todos, ?Company $company = null): array
    {
        $startTime = microtime(true);
        
        $systemPrompt = SystemPrompt::getActiveForType('todo_analysis');
        
        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for todo_analysis');
        }

        $goals = $company ? $company->goals()->with('kpis')->get() : collect();
        $topKpi = $company ? $company->top_kpi : null;

        $contextVariables = $this->buildContextVariables($company, $goals, $topKpi, $todos);
        $userPrompt = $this->buildPromptFromTemplate($systemPrompt->user_prompt_template, $contextVariables);

        // Call webhook with analysis task
        $response = $this->callWebhook([
            'task' => 'todo_analysis',
            'system_message' => $systemPrompt->system_message,
            'user_prompt' => $userPrompt,
            'temperature' => (float) $systemPrompt->temperature,
            'run_id' => $run->id,
            'company_id' => $company?->id,
        ]);

        $duration = (microtime(true) - $startTime) * 1000;

        if (!$response['success']) {
            AiLog::create([
                'run_id' => $run->id,
                'prompt_type' => 'todo_analysis',
                'system_prompt_id' => $systemPrompt->id,
                'input_context' => ['todos' => $todos->pluck('normalized_title')],
                'response' => ['error' => $response['error']],
                'duration_ms' => $duration,
                'success' => false,
                'error_message' => $response['error'],
            ]);
            
            throw new \Exception('Webhook AI request failed: ' . $response['error']);
        }

        $result = $response['data'];
        
        // Validate and enhance quality
        $validator = new AiResponseValidator();
        $validator->validateTodoAnalysis($result);
        $result = $validator->enhanceQuality($result);
        
        // Log success
        AiLog::create([
            'run_id' => $run->id,
            'prompt_type' => 'todo_analysis',
            'system_prompt_id' => $systemPrompt->id,
            'input_context' => [
                'company' => $company?->name,
                'top_kpi' => $topKpi?->name,
                'todos_count' => $todos->count(),
            ],
            'response' => $result,
            'tokens_used' => $response['tokens_used'] ?? null,
            'duration_ms' => $duration,
            'success' => true,
        ]);

        return $result;
    }

    /**
     * Extract company information from freeform text via webhook
     */
    public function extractCompanyInfo(string $text): array
    {
        $systemPrompt = SystemPrompt::getActiveForType('company_extraction');
        
        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for company_extraction');
        }

        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            ['text' => $text]
        );

        $response = $this->callWebhook([
            'task' => 'company_extraction',
            'system_message' => $systemPrompt->system_message,
            'user_prompt' => $userPrompt,
            'temperature' => (float) $systemPrompt->temperature,
        ]);

        if (!$response['success']) {
            throw new \Exception('Webhook AI request failed: ' . $response['error']);
        }

        return $response['data'];
    }

    /**
     * Extract goals and KPIs from freeform text via webhook
     */
    public function extractGoalsAndKpis(string $text): array
    {
        $systemPrompt = SystemPrompt::getActiveForType('goals_extraction');
        
        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for goals_extraction');
        }

        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            ['text' => $text]
        );

        $response = $this->callWebhook([
            'task' => 'goals_extraction',
            'system_message' => $systemPrompt->system_message,
            'user_prompt' => $userPrompt,
            'temperature' => (float) $systemPrompt->temperature,
        ]);

        if (!$response['success']) {
            throw new \Exception('Webhook AI request failed: ' . $response['error']);
        }

        return $response['data'];
    }

    /**
     * Call n8n webhook for AI processing
     */
    protected function callWebhook(array $payload): array
    {
        try {
            Log::info('WebhookAiService: Calling webhook', [
                'url' => $this->webhookUrl,
                'task' => $payload['task'] ?? 'unknown',
            ]);

            $response = Http::timeout(120)->post($this->webhookUrl, $payload);

            if ($response->failed()) {
                Log::error('WebhookAiService: Webhook call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Webhook returned status ' . $response->status() . ': ' . $response->body(),
                ];
            }

            $data = $response->json();

            // Expected response format: {success: true, data: {...}, tokens_used: 123}
            // or {success: false, error: "..."}
            if (!isset($data['success'])) {
                Log::error('WebhookAiService: Invalid webhook response format', ['response' => $data]);
                
                return [
                    'success' => false,
                    'error' => 'Invalid webhook response format',
                ];
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('WebhookAiService: Exception during webhook call', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build context variables for template replacement
     */
    protected function buildContextVariables(?Company $company, Collection $goals, ?GoalKpi $topKpi, Collection $todos = null): array
    {
        $variables = [
            'company_name' => $company?->name ?? 'Not set',
            'business_model' => $company?->business_model ? str_replace('_', ' ', $company->business_model) : 'Not set',
            'team_info' => $company ? "{$company->team_cofounders} co-founders, {$company->team_employees} employees" : 'Not set',
            'user_position' => $company?->user_position ?? 'Not set',
            'customer_profile' => $company?->customer_profile ?? 'Not specified',
            'market_insights' => $company?->market_insights ?? 'Not specified',
            'company_stage' => $this->detectCompanyStage($company, $topKpi),
        ];

        if ($topKpi) {
            $gap = $topKpi->target_value - $topKpi->current_value;
            $gapPercentage = $topKpi->target_value > 0 
                ? round((($topKpi->target_value - $topKpi->current_value) / $topKpi->target_value) * 100, 1)
                : 0;
                
            $variables['top_kpi_name'] = $topKpi->name;
            $variables['top_kpi_current'] = number_format($topKpi->current_value, 0);
            $variables['top_kpi_target'] = number_format($topKpi->target_value, 0);
            $variables['top_kpi_unit'] = $topKpi->unit;
            $variables['top_kpi_gap'] = number_format($gap, 0);
            $variables['top_kpi_gap_percentage'] = $gapPercentage;
        } else {
            $variables['top_kpi_name'] = 'No top KPI set';
            $variables['top_kpi_current'] = '—';
            $variables['top_kpi_target'] = '—';
            $variables['top_kpi_unit'] = '';
            $variables['top_kpi_gap'] = '—';
            $variables['top_kpi_gap_percentage'] = '—';
        }

        $variables['goals_list'] = $this->buildGoalsHierarchy($goals);
        $variables['goals_hierarchy'] = $this->buildGoalsHierarchy($goals);
        $variables['standalone_kpis_list'] = $company ? $this->buildStandaloneKpisList($company) : 'None';

        if ($todos) {
            $todosList = '';
            foreach ($todos as $index => $todo) {
                $todosList .= ($index + 1) . ". {$todo->normalized_title}\n";
            }
            $variables['todos_list'] = $todosList;
        }

        return $variables;
    }

    /**
     * Replace template variables with actual values
     */
    protected function buildPromptFromTemplate(string $template, array $variables): string
    {
        $result = $template;
        
        foreach ($variables as $key => $value) {
            $result = str_replace("{{{$key}}}", $value, $result);
        }
        
        return $result;
    }

    /**
     * Detect company stage based on metrics
     */
    protected function detectCompanyStage(?Company $company, ?GoalKpi $topKpi): string
    {
        if (!$company || !$topKpi) {
            return 'Early Stage';
        }

        $teamSize = ($company->team_cofounders ?? 0) + ($company->team_employees ?? 0);
        $current = $topKpi->current_value ?? 0;
        
        $isRevenue = str_contains(strtolower($topKpi->name), 'revenue') || 
                     str_contains(strtolower($topKpi->name), 'mrr') ||
                     str_contains(strtolower($topKpi->unit ?? ''), '€') ||
                     str_contains(strtolower($topKpi->unit ?? ''), '$');

        if ($isRevenue) {
            if ($current < 5000) return 'Pre-Revenue / Building';
            if ($current < 50000) return 'Early Traction';
            if ($current < 100000) return 'Scaling';
            return 'Growth Stage';
        }

        $isUsers = str_contains(strtolower($topKpi->name), 'user') ||
                   str_contains(strtolower($topKpi->name), 'customer');
        
        if ($isUsers) {
            if ($current < 100) return 'Pre-PMF / Building';
            if ($current < 1000) return 'Early Traction';
            if ($current < 10000) return 'Product-Market Fit';
            return 'Scaling';
        }

        if ($teamSize < 5) return 'Early Stage';
        if ($teamSize < 20) return 'Growing';
        return 'Scaling';
    }

    /**
     * Build hierarchical goals list
     */
    protected function buildGoalsHierarchy(Collection $goals): string
    {
        if ($goals->isEmpty()) {
            return 'No goals defined yet';
        }

        $hierarchy = '';
        $byPriority = $goals->groupBy('priority');
        
        $priorityLabels = [
            'high' => '[HIGH PRIORITY - CRITICAL]',
            'medium' => '[MEDIUM PRIORITY]',
            'low' => '[LOW PRIORITY]',
        ];

        foreach (['high', 'medium', 'low'] as $priority) {
            if (!isset($byPriority[$priority]) || $byPriority[$priority]->isEmpty()) {
                continue;
            }
            
            $hierarchy .= $priorityLabels[$priority] . "\n";
            
            $counter = 1;
            foreach ($byPriority[$priority] as $goal) {
                $hierarchy .= "→ {$counter}. {$goal->title}";
                
                if ($goal->time_frame) {
                    $hierarchy .= " ({$goal->time_frame})";
                }
                
                if ($goal->description) {
                    $hierarchy .= "\n   Description: {$goal->description}";
                }
                
                $hierarchy .= "\n";
                
                if ($goal->kpis->isNotEmpty()) {
                    foreach ($goal->kpis as $kpi) {
                        $gap = $kpi->target_value - $kpi->current_value;
                        $gapPct = $kpi->target_value > 0 
                            ? round(($gap / $kpi->target_value) * 100, 1) 
                            : 0;
                        
                        $hierarchy .= "   └─ KPI: {$kpi->name} ";
                        $hierarchy .= "({$kpi->current_value} → {$kpi->target_value} {$kpi->unit}) ";
                        $hierarchy .= "[Gap: " . number_format($gap, 0) . ", {$gapPct}% to go]";
                        
                        if ($kpi->is_top_kpi) {
                            $hierarchy .= " ⭐ TOP KPI";
                        }
                        
                        $hierarchy .= "\n";
                    }
                }
                
                $counter++;
            }
            
            $hierarchy .= "\n";
        }

        $noPriority = $goals->whereNull('priority');
        if ($noPriority->isNotEmpty()) {
            $hierarchy .= "[NO PRIORITY SET]\n";
            $counter = 1;
            foreach ($noPriority as $goal) {
                $hierarchy .= "→ {$counter}. {$goal->title}\n";
                if ($goal->kpis->isNotEmpty()) {
                    foreach ($goal->kpis as $kpi) {
                        $hierarchy .= "   └─ KPI: {$kpi->name} ({$kpi->current_value} → {$kpi->target_value} {$kpi->unit})\n";
                    }
                }
                $counter++;
            }
        }

        return trim($hierarchy);
    }

    /**
     * Build standalone KPIs list
     */
    protected function buildStandaloneKpisList(Company $company): string
    {
        $standaloneKpis = $company->kpis;
        
        if ($standaloneKpis->isEmpty()) {
            return 'None';
        }

        $list = '';
        
        foreach ($standaloneKpis as $kpi) {
            $gap = $kpi->target_value - $kpi->current_value;
            $gapPct = $kpi->target_value > 0 
                ? round(($gap / $kpi->target_value) * 100, 1) 
                : 0;
            
            $list .= "→ {$kpi->name}: ";
            $list .= "{$kpi->current_value} → {$kpi->target_value} {$kpi->unit} ";
            $list .= "[Gap: " . number_format($gap, 0) . ", {$gapPct}% to go]";
            
            if ($kpi->is_top_kpi) {
                $list .= " ⭐ TOP KPI";
            }
            
            $list .= "\n";
        }

        return trim($list);
    }
}
