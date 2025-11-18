<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\Run;
use App\Models\Todo;
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
        $goals = $company ? $company->goals()->with('kpis')->get() : collect();
        $topKpi = $company ? $company->topKpi() : null;

        $prompt = $this->buildAnalysisPrompt($todos, $goals, $topKpi, $company);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI Focus Coach helping early-stage founders prioritize tasks based on their business goals and KPIs. Provide structured, actionable feedback.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        $result = json_decode($response->json()['choices'][0]['message']['content'], true);

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
}

