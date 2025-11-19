<?php

namespace App\Livewire;

use App\Models\SystemPrompt;
use App\Services\MockDataGenerator;
use App\Services\OpenAiAnalysisService;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PromptTester extends Component
{
    public $promptType = 'todo_analysis';
    public $useMockData = true;
    public $testInput = '';
    public $testing = false;
    public $result = null;
    public $error = null;

    public function mount()
    {
        $mockGen = new MockDataGenerator();
        $this->testInput = implode("\n", $mockGen->generateMockTodos());
    }

    public function test()
    {
        $this->testing = true;
        $this->result = null;
        $this->error = null;

        try {
            $systemPrompt = SystemPrompt::getActiveForType($this->promptType);
            
            if (!$systemPrompt) {
                throw new \Exception('No active prompt found for ' . $this->promptType);
            }

            if ($this->promptType === 'todo_analysis') {
                $this->testTodoAnalysis($systemPrompt);
            } elseif ($this->promptType === 'company_extraction') {
                $this->testCompanyExtraction($systemPrompt);
            } else {
                $this->testGoalsExtraction($systemPrompt);
            }

            $this->testing = false;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->testing = false;
        }
    }

    protected function testTodoAnalysis($systemPrompt)
    {
        $service = new OpenAiAnalysisService();
        
        if ($this->useMockData) {
            $mockGen = new MockDataGenerator();
            $mockContext = $mockGen->generateMockContext();
            
            // Build context variables
            $variables = [
                'company_name' => $mockContext->company->name,
                'business_model' => $mockContext->company->business_model,
                'team_info' => "{$mockContext->company->team_cofounders} founders, {$mockContext->company->team_employees} employees",
                'user_position' => $mockContext->company->user_position,
                'top_kpi_name' => $mockContext->top_kpi->name,
                'top_kpi_current' => $mockContext->top_kpi->current,
                'top_kpi_target' => $mockContext->top_kpi->target,
                'top_kpi_unit' => $mockContext->top_kpi->unit,
                'top_kpi_gap' => $mockContext->top_kpi->target - $mockContext->top_kpi->current,
                'top_kpi_gap_percentage' => round((($mockContext->top_kpi->target - $mockContext->top_kpi->current) / $mockContext->top_kpi->target) * 100, 1),
                'goals_list' => collect($mockContext->goals)->map(fn($g, $i) => ($i+1) . ". {$g->title}")->implode("\n"),
                'todos_list' => $this->testInput,
            ];
            
            $userPrompt = $systemPrompt->user_prompt_template;
            foreach ($variables as $key => $value) {
                $userPrompt = str_replace("{{{$key}}}", $value, $userPrompt);
            }
        } else {
            // Use live context
            $company = auth()->user()->company;
            $userPrompt = "Todos:\n" . $this->testInput;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('services.openai.model'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt->system_message],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $systemPrompt->temperature,
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        $this->result = [
            'raw' => $response->json(),
            'parsed' => json_decode($response->json()['choices'][0]['message']['content'], true),
            'tokens' => $response->json()['usage']['total_tokens'] ?? null,
        ];
    }

    protected function testCompanyExtraction($systemPrompt)
    {
        $userPrompt = str_replace('{{text}}', $this->testInput, $systemPrompt->user_prompt_template);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('services.openai.model'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt->system_message],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $systemPrompt->temperature,
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        $this->result = [
            'raw' => $response->json(),
            'parsed' => json_decode($response->json()['choices'][0]['message']['content'], true),
            'tokens' => $response->json()['usage']['total_tokens'] ?? null,
        ];
    }

    protected function testGoalsExtraction($systemPrompt)
    {
        $userPrompt = str_replace('{{text}}', $this->testInput, $systemPrompt->user_prompt_template);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('services.openai.model'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt->system_message],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $systemPrompt->temperature,
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        $this->result = [
            'raw' => $response->json(),
            'parsed' => json_decode($response->json()['choices'][0]['message']['content'], true),
            'tokens' => $response->json()['usage']['total_tokens'] ?? null,
        ];
    }

    public function render()
    {
        $activePrompt = SystemPrompt::getActiveForType($this->promptType);

        return view('livewire.prompt-tester', [
            'activePrompt' => $activePrompt,
        ])->layout('components.layouts.app');
    }
}

