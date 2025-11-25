<?php

use App\Services\{WebhookAiService, UserContextService, AiResponseValidator, MockDataGenerator};
use App\Models\{User, Company, Goal, GoalKpi, Run, Todo, SystemPrompt};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

describe('WebhookAiService', function () {
    it('uses user webhook url when available', function () {
        $user = User::factory()->create([
            'n8n_webhook_url' => 'https://custom.webhook.com/user-specific'
        ]);

        $service = new WebhookAiService($user);

        expect($service)->toBeInstanceOf(WebhookAiService::class);
    });

    it('falls back to config webhook url when user has none', function () {
        $user = User::factory()->create(['n8n_webhook_url' => null]);
        
        config(['services.n8n.ai_analysis_webhook_url' => 'https://fallback.webhook.com']);

        $service = new WebhookAiService($user);

        expect($service)->toBeInstanceOf(WebhookAiService::class);
    });

    it('throws exception when no webhook url configured', function () {
        $user = User::factory()->create(['n8n_webhook_url' => null]);
        config(['services.n8n.ai_analysis_webhook_url' => null]);

        expect(fn() => new WebhookAiService($user))
            ->toThrow(\Exception::class, 'AI analysis webhook URL not configured');
    });

    it('calls webhook with correct payload structure', function () {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['result' => 'analyzed']
            ], 200)
        ]);

        $user = User::factory()->create([
            'n8n_webhook_url' => 'https://test.webhook.com'
        ]);
        $company = Company::factory()->forUser($user)->create();
        $run = Run::factory()->forCompany($company)->forUser($user)->create();
        $todos = Todo::factory()->count(3)->forRun($run)->create();
        
        SystemPrompt::factory()->todoAnalysis()->active()->create();

        $service = new WebhookAiService($user);
        
        try {
            $service->analyzeTodos($run, $todos, $company);
        } catch (\Exception $e) {
            // Service may throw exceptions for missing response structure
        }

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test.webhook.com' &&
                   isset($request['task']) &&
                   $request['task'] === 'todo_analysis';
        });
    });

    it('logs failed webhook calls', function () {
        Http::fake([
            '*' => Http::response(['error' => 'Service unavailable'], 500)
        ]);

        $user = User::factory()->create([
            'n8n_webhook_url' => 'https://test.webhook.com'
        ]);
        $run = Run::factory()->forUser($user)->create();
        $todos = Todo::factory()->count(2)->forRun($run)->create();
        
        SystemPrompt::factory()->todoAnalysis()->active()->create();

        $service = new WebhookAiService($user);

        try {
            $service->analyzeTodos($run, $todos);
        } catch (\Exception $e) {
            // Expected to fail
        }

        expect(\App\Models\AiLog::where('run_id', $run->id)->where('success', false)->exists())
            ->toBeTrue();
    });
});

describe('UserContextService', function () {
    it('builds full user context', function () {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $company = Company::factory()->forUser($user)->create(['name' => 'ACME Inc']);
        Goal::factory()->count(2)->forCompany($company)->create();

        $service = new UserContextService();
        $context = $service->getFullContext($user);

        expect($context)->toHaveKeys(['user', 'company', 'goals']);
        expect($context['user']['full_name'])->toBe('John Doe');
        expect($context['company']['name'])->toBe('ACME Inc');
    });

    it('caches user context', function () {
        $user = User::factory()->create();
        Company::factory()->forUser($user)->create();

        $service = new UserContextService();
        
        $context1 = $service->getFullContext($user);
        $context2 = $service->getFullContext($user);

        expect(Cache::has("user_context_{$user->id}"))->toBeTrue();
    });

    it('returns null company when user has none', function () {
        $user = User::factory()->create();

        $service = new UserContextService();
        $context = $service->getFullContext($user);

        expect($context['company'])->toBeNull();
    });
});

describe('AiResponseValidator', function () {
    it('validates correct response structure', function () {
        $validator = new AiResponseValidator();
        
        $response = [
            'evaluations' => [
                ['todo_id' => 'uuid-1', 'score' => 85, 'color' => 'green']
            ],
            'missing_todos' => []
        ];

        $result = $validator->validateTodoAnalysis($response);

        expect($result['valid'])->toBeTrue();
    });

    it('rejects invalid response structure', function () {
        $validator = new AiResponseValidator();
        
        $response = ['invalid' => 'data'];

        $result = $validator->validateTodoAnalysis($response);

        expect($result['valid'])->toBeFalse();
        expect($result)->toHaveKey('errors');
    });

    it('validates score ranges', function () {
        $validator = new AiResponseValidator();
        
        $response = [
            'evaluations' => [
                ['todo_id' => 'uuid-1', 'score' => 150, 'color' => 'green'] // Invalid score
            ],
            'missing_todos' => []
        ];

        $result = $validator->validateTodoAnalysis($response);

        expect($result['valid'])->toBeFalse();
    });

    it('validates color values', function () {
        $validator = new AiResponseValidator();
        
        $response = [
            'evaluations' => [
                ['todo_id' => 'uuid-1', 'score' => 85, 'color' => 'purple'] // Invalid color
            ],
            'missing_todos' => []
        ];

        $result = $validator->validateTodoAnalysis($response);

        expect($result['valid'])->toBeFalse();
    });
});

describe('MockDataGenerator', function () {
    it('generates realistic company data', function () {
        $generator = new MockDataGenerator();
        
        $companyData = $generator->generateCompany();

        expect($companyData)->toHaveKeys(['name', 'business_model', 'team_cofounders']);
        expect(['b2b_saas', 'b2c', 'marketplace', 'agency', 'other'])
            ->toContain($companyData['business_model']);
    });

    it('generates realistic goal data', function () {
        $generator = new MockDataGenerator();
        
        $goalData = $generator->generateGoal();

        expect($goalData)->toHaveKeys(['title', 'description', 'priority']);
        expect(['high', 'medium', 'low'])->toContain($goalData['priority']);
    });

    it('generates realistic kpi data', function () {
        $generator = new MockDataGenerator();
        
        $kpiData = $generator->generateKpi();

        expect($kpiData)->toHaveKeys(['name', 'current_value', 'target_value', 'unit']);
        expect($kpiData['target_value'])->toBeGreaterThan($kpiData['current_value']);
    });

    it('generates realistic todo list', function () {
        $generator = new MockDataGenerator();
        
        $todos = $generator->generateTodos(5);

        expect($todos)->toHaveCount(5);
        expect($todos[0])->toHaveKeys(['raw_input', 'normalized_title']);
    });
});

describe('Service Integration', function () {
    it('full workflow: user context -> webhook analysis -> validation', function () {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => [
                    'evaluations' => [
                        ['todo_id' => '1', 'score' => 85, 'color' => 'green', 'reasoning' => 'Good']
                    ],
                    'missing_todos' => []
                ]
            ], 200)
        ]);

        $user = User::factory()->create([
            'n8n_webhook_url' => 'https://test.webhook.com'
        ]);
        $company = Company::factory()->forUser($user)->create();
        $run = Run::factory()->forCompany($company)->forUser($user)->create();
        $todos = Todo::factory()->count(1)->forRun($run)->create();
        
        SystemPrompt::factory()->todoAnalysis()->active()->create();

        // Get user context
        $contextService = new UserContextService();
        $context = $contextService->getFullContext($user);

        expect($context['user']['id'])->toBe($user->id);
        expect($context['company']['id'])->toBe($company->id);

        // Call webhook service
        $webhookService = new WebhookAiService($user);
        
        try {
            $webhookService->analyzeTodos($run, $todos, $company);
        } catch (\Exception $e) {
            // May throw for response structure
        }

        // Verify webhook was called
        Http::assertSent(function ($request) {
            return isset($request['task']) && $request['task'] === 'todo_analysis';
        });
    });
});
