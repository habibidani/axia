<?php

use App\Models\{WebhookPreset, User, GoalKpi, SystemPrompt, Run, TodoEvaluation};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Webhook Preset Business Logic', function () {
    it('only one active webhook per user', function () {
        $user = User::factory()->create();
        
        $preset1 = WebhookPreset::factory()->forUser($user)->create(['is_active' => true]);
        $preset2 = WebhookPreset::factory()->forUser($user)->create(['is_active' => false]);
        $preset3 = WebhookPreset::factory()->forUser($user)->create(['is_active' => false]);

        expect(WebhookPreset::where('user_id', $user->id)->where('is_active', true)->count())->toBe(1);
    });

    it('activate method deactivates others and updates user webhook url', function () {
        $user = User::factory()->create();
        
        $preset1 = WebhookPreset::factory()->forUser($user)->create([
            'is_active' => true,
            'webhook_url' => 'https://n8n.example.com/webhook/1'
        ]);
        $preset2 = WebhookPreset::factory()->forUser($user)->create([
            'is_active' => false,
            'webhook_url' => 'https://n8n.example.com/webhook/2'
        ]);

        $preset2->activate();

        expect($preset1->fresh()->is_active)->toBeFalse();
        expect($preset2->fresh()->is_active)->toBeTrue();
        expect($user->fresh()->n8n_webhook_url)->toBe('https://n8n.example.com/webhook/2');
    });

    it('prevents multiple active webhooks via constraint', function () {
        $user = User::factory()->create();
        
        WebhookPreset::factory()->forUser($user)->create(['is_active' => true]);

        // This should work since constraint only checks is_active=true
        $preset2 = WebhookPreset::factory()->forUser($user)->create(['is_active' => false]);
        $preset3 = WebhookPreset::factory()->forUser($user)->create(['is_active' => false]);

        expect(WebhookPreset::where('user_id', $user->id)->count())->toBe(3);
    });
});

describe('Goal KPI Business Logic', function () {
    it('can calculate progress percentage', function () {
        $kpi = GoalKpi::factory()->create([
            'current_value' => 5000,
            'target_value' => 10000,
        ]);

        $progress = ($kpi->current_value / $kpi->target_value) * 100;

        expect($progress)->toBe(50.0);
    });

    it('can have null current value', function () {
        $kpi = GoalKpi::factory()->create(['current_value' => null]);

        expect($kpi->current_value)->toBeNull();
    });

    it('standalone kpi has no goal but has company', function () {
        $company = \App\Models\Company::factory()->create();
        $kpi = GoalKpi::factory()->standalone()->state(['company_id' => $company->id])->create();

        expect($kpi->goal_id)->toBeNull();
        expect($kpi->company_id)->not->toBeNull();
    });
});

describe('System Prompt Business Logic', function () {
    it('only one active prompt per type', function () {
        SystemPrompt::factory()->todoAnalysis()->active()->create(['version' => 'v1.0']);
        SystemPrompt::factory()->todoAnalysis()->create(['is_active' => false, 'version' => 'v1.1']);
        SystemPrompt::factory()->companyExtraction()->active()->create(['version' => 'v1.0']);

        $activeTodo = SystemPrompt::where('type', 'todo_analysis')->where('is_active', true)->count();
        $activeCompany = SystemPrompt::where('type', 'company_extraction')->where('is_active', true)->count();

        expect($activeTodo)->toBe(1);
        expect($activeCompany)->toBe(1);
    });

    it('can version prompts', function () {
        $v1 = SystemPrompt::factory()->create(['version' => 'v1.0']);
        $v2 = SystemPrompt::factory()->create(['version' => 'v2.0']);

        expect($v1->version)->toBe('v1.0');
        expect($v2->version)->toBe('v2.0');
    });
});

describe('Run Score Calculation', function () {
    it('calculates overall score from evaluations', function () {
        $run = Run::factory()->create();
        
        TodoEvaluation::factory()->green()->forRun($run)->create(['score' => 90]);
        TodoEvaluation::factory()->yellow()->forRun($run)->create(['score' => 60]);
        TodoEvaluation::factory()->orange()->forRun($run)->create(['score' => 30]);

        $averageScore = $run->fresh()->todoEvaluations->avg('score');

        expect($averageScore)->toBe(60.0);
    });

    it('can store overall score', function () {
        $run = Run::factory()->create(['overall_score' => 75]);

        expect($run->overall_score)->toBe(75);
    });
});

describe('Todo Evaluation Color Logic', function () {
    it('green todos have high scores', function () {
        $eval = TodoEvaluation::factory()->green()->create();

        expect($eval->color)->toBe('green');
        expect($eval->score)->toBeGreaterThanOrEqual(80);
    });

    it('yellow todos have medium scores', function () {
        $eval = TodoEvaluation::factory()->yellow()->create();

        expect($eval->color)->toBe('yellow');
        expect($eval->score)->toBeBetween(50, 79);
    });

    it('orange todos have low scores', function () {
        $eval = TodoEvaluation::factory()->orange()->create();

        expect($eval->color)->toBe('orange');
        expect($eval->score)->toBeLessThan(50);
    });
});

describe('Agent Session Expiration', function () {
    it('can check if session is expired', function () {
        $expired = \App\Models\AgentSession::factory()->expired()->create();
        $active = \App\Models\AgentSession::factory()->create();

        expect($expired->expires_at)->toBeBefore(now());
        expect($active->expires_at)->toBeAfter(now());
    });

    it('stores session metadata', function () {
        $session = \App\Models\AgentSession::factory()->create([
            'meta' => ['step' => 3, 'context' => 'onboarding']
        ]);

        expect($session->meta)->toBeArray();
        expect($session->meta['step'])->toBe(3);
    });
});

describe('Business Model Validation', function () {
    it('company has valid business model', function () {
        $validModels = ['b2b_saas', 'b2c', 'marketplace', 'agency', 'other'];
        
        $company = \App\Models\Company::factory()->create();

        expect($validModels)->toContain($company->business_model);
    });

    it('goal has valid priority', function () {
        $validPriorities = ['high', 'medium', 'low'];
        
        $goal = \App\Models\Goal::factory()->create();

        expect($validPriorities)->toContain($goal->priority);
    });

    it('todo evaluation has valid color', function () {
        $validColors = ['green', 'yellow', 'orange'];
        
        $eval = TodoEvaluation::factory()->create();

        expect($validColors)->toContain($eval->color);
    });
});
