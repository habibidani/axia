<?php

namespace App\Services;

use App\Models\AiLog;
use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\Run;
use App\Models\SystemPrompt;
use App\Models\Todo;
use App\Services\AiResponseValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class OpenAiAnalysisService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model');
    }

    /**
     * Analyze todos against goals and KPIs
     */
    public function analyzeTodos(Run $run, Collection $todos, ?Company $company = null): array
    {
        $startTime = microtime(true);
        
        // Get active prompt from DB
        $systemPrompt = SystemPrompt::getActiveForType('todo_analysis');
        
        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for todo_analysis');
        }

        $goals = $company ? $company->goals()->with('kpis')->get() : collect();
        $topKpi = $company ? $company->top_kpi : null;

        // Build context with variables
        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            $this->buildContextVariables($company, $goals, $topKpi, $todos)
        );

        $requestData = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt->system_message
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        // Only add temperature if model supports it
        // Some models (o1, o3, etc.) only support temperature=1 or don't support it at all
        // We check the model name and only add temperature for models that support custom values
        $modelName = strtolower($this->model);
        $modelsWithoutCustomTemperature = [
            'o1', 'o3', // Reasoning models
        ];
        
        $modelSupportsTemperature = true;
        foreach ($modelsWithoutCustomTemperature as $prefix) {
            if (str_starts_with($modelName, $prefix)) {
                $modelSupportsTemperature = false;
                break;
            }
        }
        
        if ($modelSupportsTemperature) {
            $requestData['temperature'] = (float) $systemPrompt->temperature;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', $requestData);

        // If we get a temperature error, retry without temperature (using default)
        if ($response->failed()) {
            $errorBody = json_decode($response->body(), true);
            $isTemperatureError = isset($errorBody['error']['code']) && 
                                  $errorBody['error']['code'] === 'unsupported_value' &&
                                  isset($errorBody['error']['param']) &&
                                  $errorBody['error']['param'] === 'temperature';
            
            if ($isTemperatureError && isset($requestData['temperature'])) {
                // Retry without temperature parameter (will use default)
                unset($requestData['temperature']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', $requestData);
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        if ($response->failed()) {
            // Log failure
            AiLog::create([
                'run_id' => $run->id,
                'prompt_type' => 'todo_analysis',
                'system_prompt_id' => $systemPrompt->id,
                'input_context' => ['todos' => $todos->pluck('normalized_title')],
                'response' => ['error' => $response->body()],
                'duration_ms' => $duration,
                'success' => false,
                'error_message' => $response->body(),
            ]);
            
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $result = json_decode($response->json()['choices'][0]['message']['content'], true);
        
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
            'tokens_used' => $response->json()['usage']['total_tokens'] ?? null,
            'duration_ms' => $duration,
            'success' => true,
        ]);

        return $result;
    }

    /**
     * Build the analysis prompt
     */
    protected function buildAnalysisPrompt(Collection $todos, Collection $goals, ?GoalKpi $topKpi, ?Company $company): string
    {
        $context = "Context:\n";

        if ($company) {
            $context .= "Company: {$company->name}\n";
            if ($company->business_model) {
                $context .= "Business Model: {$company->business_model}\n";
            }
            if ($company->team_cofounders || $company->team_employees) {
                $context .= "Team: {$company->team_cofounders} co-founders, {$company->team_employees} employees\n";
            }
        }

        if ($topKpi) {
            $context .= "\nTop KPI: {$topKpi->name}\n";
            $context .= "Current: {$topKpi->current_value} {$topKpi->unit}\n";
            $context .= "Target: {$topKpi->target_value} {$topKpi->unit}\n";
        }

        if ($goals->isNotEmpty()) {
            $context .= "\nGoals:\n";
            foreach ($goals as $goal) {
                $context .= "- {$goal->title}";
                if ($goal->description) {
                    $context .= ": {$goal->description}";
                }
                $context .= "\n";
                
                if ($goal->kpis->isNotEmpty()) {
                    foreach ($goal->kpis as $kpi) {
                        $context .= "  * KPI: {$kpi->name} ({$kpi->current_value} → {$kpi->target_value} {$kpi->unit})\n";
                    }
                }
            }
        }

        $context .= "\nTasks to evaluate:\n";
        foreach ($todos as $index => $todo) {
            $context .= ($index + 1) . ". {$todo->normalized_title}\n";
        }

        $instructions = "\n\nFor each task, provide:\n";
        $instructions .= "1. A score from 0-100 based on impact on the top KPI and goals\n";
        $instructions .= "2. A color: 'green' (high impact, 70-100), 'yellow' (medium, 40-69), 'orange' (low, 0-39)\n";
        $instructions .= "3. A 1-2 sentence reasoning explaining the score\n";
        $instructions .= "4. A priority_recommendation: 'high', 'low', or 'none'\n";
        $instructions .= "5. An action_recommendation: 'keep' (high priority), 'delegate' (medium), or 'drop' (low impact)\n";
        $instructions .= "6. If delegate, suggest a delegation_target_role\n";
        $instructions .= "7. Link to the most relevant goal_id and kpi_id if applicable\n\n";

        $instructions .= "Also suggest 2-3 missing high-impact tasks with:\n";
        $instructions .= "- title\n";
        $instructions .= "- description\n";
        $instructions .= "- category (hiring, prioritization, delegation, culture, other)\n";
        $instructions .= "- impact_score (0-100)\n";
        $instructions .= "- suggested_owner_role\n";
        $instructions .= "- goal_id and kpi_id if applicable\n\n";

        $instructions .= "Provide an overall_score (0-100) for focus quality and a summary_text (2-3 sentences).\n\n";

        $instructions .= "Return as JSON with structure:\n";
        $instructions .= "{\n";
        $instructions .= "  \"overall_score\": 75,\n";
        $instructions .= "  \"summary_text\": \"...\",\n";
        $instructions .= "  \"evaluations\": [\n";
        $instructions .= "    {\n";
        $instructions .= "      \"task_index\": 0,\n";
        $instructions .= "      \"score\": 85,\n";
        $instructions .= "      \"color\": \"green\",\n";
        $instructions .= "      \"reasoning\": \"...\",\n";
        $instructions .= "      \"priority_recommendation\": \"high\",\n";
        $instructions .= "      \"action_recommendation\": \"keep\",\n";
        $instructions .= "      \"delegation_target_role\": null,\n";
        $instructions .= "      \"goal_title\": \"Goal title or null\",\n";
        $instructions .= "      \"kpi_name\": \"KPI name or null\"\n";
        $instructions .= "    }\n";
        $instructions .= "  ],\n";
        $instructions .= "  \"missing_todos\": [\n";
        $instructions .= "    {\n";
        $instructions .= "      \"title\": \"...\",\n";
        $instructions .= "      \"description\": \"...\",\n";
        $instructions .= "      \"category\": \"hiring\",\n";
        $instructions .= "      \"impact_score\": 90,\n";
        $instructions .= "      \"suggested_owner_role\": \"CEO\",\n";
        $instructions .= "      \"goal_title\": \"Goal title or null\",\n";
        $instructions .= "      \"kpi_name\": \"KPI name or null\"\n";
        $instructions .= "    }\n";
        $instructions .= "  ]\n";
        $instructions .= "}";

        return $context . $instructions;
    }

    /**
     * Extract company information from freeform text
     */
    public function extractCompanyInfo(string $text): array
    {
        // Get active prompt from DB
        $systemPrompt = SystemPrompt::getActiveForType('company_extraction');
        
        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for company_extraction');
        }

        // Build user prompt from template
        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            ['text' => $text]
        );

        $requestData = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt->system_message
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        // Only add temperature if model supports it
        // Some models (o1, o3, gpt-4o-mini, etc.) only support temperature=1 or don't support it at all
        // We check the model name and only add temperature for models that support custom values
        $modelName = strtolower($this->model);
        $modelsWithoutCustomTemperature = [
            'o1', 'o3', // Reasoning models
            'gpt-4o-mini', 'gpt-4o-mini-2024', // Some models only support temperature=1
        ];
        
        $modelSupportsTemperature = true;
        foreach ($modelsWithoutCustomTemperature as $prefix) {
            if (str_contains($modelName, $prefix)) {
                $modelSupportsTemperature = false;
                break;
            }
        }
        
        if ($modelSupportsTemperature && (float) $systemPrompt->temperature != 1.0) {
            $requestData['temperature'] = (float) $systemPrompt->temperature;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', $requestData);

        // If we get a temperature error, retry without temperature (using default)
        if ($response->failed()) {
            $errorBody = json_decode($response->body(), true);
            $isTemperatureError = isset($errorBody['error']['code']) && 
                                  $errorBody['error']['code'] === 'unsupported_value' &&
                                  isset($errorBody['error']['param']) &&
                                  $errorBody['error']['param'] === 'temperature';
            
            if ($isTemperatureError && isset($requestData['temperature'])) {
                // Retry without temperature parameter (will use default)
                unset($requestData['temperature']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', $requestData);
            }
            
            if ($response->failed()) {
                throw new \Exception('OpenAI API request failed: ' . $response->body());
            }
        }

        return json_decode($response->json()['choices'][0]['message']['content'], true);
    }

    /**
     * Extract goals and KPIs from freeform text
     */
    public function extractGoalsAndKpis(string $text): array
    {
        // Get active prompt from DB
        $systemPrompt = SystemPrompt::getActiveForType('goals_extraction');
        
        if (!$systemPrompt) {
            throw new \Exception('No active system prompt found for goals_extraction');
        }

        // Build user prompt from template
        $userPrompt = $this->buildPromptFromTemplate(
            $systemPrompt->user_prompt_template,
            ['text' => $text]
        );

        $requestData = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt->system_message
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        // Only add temperature if model supports it
        // Some models (o1, o3, gpt-4o-mini, etc.) only support temperature=1 or don't support it at all
        // We check the model name and only add temperature for models that support custom values
        $modelName = strtolower($this->model);
        $modelsWithoutCustomTemperature = [
            'o1', 'o3', // Reasoning models
            'gpt-4o-mini', 'gpt-4o-mini-2024', // Some models only support temperature=1
        ];
        
        $modelSupportsTemperature = true;
        foreach ($modelsWithoutCustomTemperature as $prefix) {
            if (str_contains($modelName, $prefix)) {
                $modelSupportsTemperature = false;
                break;
            }
        }
        
        if ($modelSupportsTemperature && (float) $systemPrompt->temperature != 1.0) {
            $requestData['temperature'] = (float) $systemPrompt->temperature;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', $requestData);

        // If we get a temperature error, retry without temperature (using default)
        if ($response->failed()) {
            $errorBody = json_decode($response->body(), true);
            $isTemperatureError = isset($errorBody['error']['code']) && 
                                  $errorBody['error']['code'] === 'unsupported_value' &&
                                  isset($errorBody['error']['param']) &&
                                  $errorBody['error']['param'] === 'temperature';
            
            if ($isTemperatureError && isset($requestData['temperature'])) {
                // Retry without temperature parameter (will use default)
                unset($requestData['temperature']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', $requestData);
            }
            
            if ($response->failed()) {
                throw new \Exception('OpenAI API request failed: ' . $response->body());
            }
        }

        return json_decode($response->json()['choices'][0]['message']['content'], true);
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

        // Build hierarchical goals list
        $variables['goals_list'] = $this->buildGoalsHierarchy($goals);
        $variables['goals_hierarchy'] = $this->buildGoalsHierarchy($goals);
        
        // Build standalone KPIs list
        $variables['standalone_kpis_list'] = $company ? $this->buildStandaloneKpisList($company) : 'None';

        // Build todos list
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

        // Simple heuristics based on top KPI and team size
        $teamSize = ($company->team_cofounders ?? 0) + ($company->team_employees ?? 0);
        $current = $topKpi->current_value ?? 0;
        
        // Check if it's a revenue metric
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

        // Check if it's user metric
        $isUsers = str_contains(strtolower($topKpi->name), 'user') ||
                   str_contains(strtolower($topKpi->name), 'customer');
        
        if ($isUsers) {
            if ($current < 100) return 'Pre-PMF / Building';
            if ($current < 1000) return 'Early Traction';
            if ($current < 10000) return 'Product-Market Fit';
            return 'Scaling';
        }

        // Default based on team size
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
        
        // Group by priority
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
                
                // Add KPIs with gaps
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

        // Add goals without priority
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
        $standaloneKpis = $company->kpis; // Only KPIs without goal_id
        
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


