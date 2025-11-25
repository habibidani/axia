<?php

use App\Models\{Company, Goal, GoalKpi, Run, Todo, TodoEvaluation, MissingTodo, SystemPrompt, AiLog, AgentSession};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Company CRUD', function () {
    it('can create company', function () {
        $company = Company::factory()->create(['name' => 'Test Company']);

        expect(Company::count())->toBe(1);
        expect($company->name)->toBe('Test Company');
    });

    it('can read company', function () {
        $company = Company::factory()->create();
        $found = Company::find($company->id);

        expect($found->id)->toBe($company->id);
    });

    it('can update company', function () {
        $company = Company::factory()->create(['name' => 'Old Name']);
        $company->update(['name' => 'New Name']);

        expect($company->fresh()->name)->toBe('New Name');
    });

    it('can delete company', function () {
        $company = Company::factory()->create();
        expect(Company::count())->toBe(1);

        $company->delete();
        expect(Company::count())->toBe(0);
    });
});

describe('Goal CRUD', function () {
    it('can create goal', function () {
        $goal = Goal::factory()->create(['title' => 'Increase MRR']);

        expect(Goal::count())->toBe(1);
        expect($goal->title)->toBe('Increase MRR');
    });

    it('can filter active goals', function () {
        Goal::factory()->active()->count(3)->create();
        Goal::factory()->inactive()->count(2)->create();

        expect(Goal::where('is_active', true)->count())->toBe(3);
    });

    it('can update goal priority', function () {
        $goal = Goal::factory()->create(['priority' => 'low']);
        $goal->update(['priority' => 'high']);

        expect($goal->fresh()->priority)->toBe('high');
    });

    it('can delete goal', function () {
        $goal = Goal::factory()->create();
        $goal->delete();

        expect(Goal::count())->toBe(0);
    });
});

describe('GoalKpi CRUD', function () {
    it('can create kpi for goal', function () {
        $goal = Goal::factory()->create();
        $kpi = GoalKpi::factory()->forGoal($goal)->create(['name' => 'MRR']);

        expect($kpi->name)->toBe('MRR');
        expect($kpi->goal_id)->toBe($goal->id);
    });

    it('can create standalone kpi', function () {
        $company = Company::factory()->create();
        $kpi = GoalKpi::factory()->standalone()->state(['company_id' => $company->id])->create();

        expect($kpi->goal_id)->toBeNull();
        expect($kpi->company_id)->toBe($company->id);
    });

    it('can update kpi values', function () {
        $kpi = GoalKpi::factory()->create(['current_value' => 1000]);
        $kpi->update(['current_value' => 1500]);

        expect($kpi->fresh()->current_value)->toBe('1500.00');
    });

    it('can mark as top kpi', function () {
        $kpi = GoalKpi::factory()->create(['is_top_kpi' => false]);
        $kpi->update(['is_top_kpi' => true]);

        expect($kpi->fresh()->is_top_kpi)->toBeTrue();
    });
});

describe('Run CRUD', function () {
    it('can create run', function () {
        $run = Run::factory()->create();

        expect(Run::count())->toBe(1);
        expect($run->period_start)->not->toBeNull();
    });

    it('can update overall score', function () {
        $run = Run::factory()->create(['overall_score' => 50]);
        $run->update(['overall_score' => 85]);

        expect($run->fresh()->overall_score)->toBe(85);
    });

    it('can associate top kpi snapshot', function () {
        $kpi = GoalKpi::factory()->topKpi()->create();
        $run = Run::factory()->create(['snapshot_top_kpi_id' => $kpi->id]);

        expect($run->snapshot_top_kpi_id)->toBe($kpi->id);
    });
});

describe('Todo CRUD', function () {
    it('can create todo from paste', function () {
        $todo = Todo::factory()->fromPaste()->create();

        expect($todo->source)->toBe('paste');
    });

    it('can create todo from csv', function () {
        $todo = Todo::factory()->fromCsv()->create();

        expect($todo->source)->toBe('csv');
    });

    it('can update normalized title', function () {
        $todo = Todo::factory()->create(['normalized_title' => 'Old Title']);
        $todo->update(['normalized_title' => 'New Title']);

        expect($todo->fresh()->normalized_title)->toBe('New Title');
    });

    it('can set owner', function () {
        $todo = Todo::factory()->withOwner('John Doe')->create();

        expect($todo->owner)->toBe('John Doe');
    });

    it('can set due date', function () {
        $todo = Todo::factory()->withDueDate('2025-12-31')->create();

        expect($todo->due_date)->toBe('2025-12-31');
    });
});

describe('TodoEvaluation CRUD', function () {
    it('can create green evaluation', function () {
        $eval = TodoEvaluation::factory()->green()->create();

        expect($eval->color)->toBe('green');
        expect($eval->score)->toBeGreaterThanOrEqual(80);
    });

    it('can create yellow evaluation', function () {
        $eval = TodoEvaluation::factory()->yellow()->create();

        expect($eval->color)->toBe('yellow');
    });

    it('can create orange evaluation', function () {
        $eval = TodoEvaluation::factory()->orange()->create();

        expect($eval->color)->toBe('orange');
        expect($eval->score)->toBeLessThan(50);
    });

    it('can set delegation target', function () {
        $eval = TodoEvaluation::factory()->create([
            'action_recommendation' => 'delegate',
            'delegation_target_role' => 'Project Manager'
        ]);

        expect($eval->delegation_target_role)->toBe('Project Manager');
    });
});

describe('MissingTodo CRUD', function () {
    it('can create missing todo', function () {
        $missing = MissingTodo::factory()->create(['title' => 'Hire Developer']);

        expect($missing->title)->toBe('Hire Developer');
    });

    it('can create hiring category', function () {
        $missing = MissingTodo::factory()->hiring()->create();

        expect($missing->category)->toBe('hiring');
        expect($missing->suggested_owner_role)->toBe('HR Manager');
    });

    it('can set high impact score', function () {
        $missing = MissingTodo::factory()->highImpact()->create();

        expect($missing->impact_score)->toBeGreaterThanOrEqual(80);
    });
});

describe('SystemPrompt CRUD', function () {
    it('can create todo analysis prompt', function () {
        $prompt = SystemPrompt::factory()->todoAnalysis()->create();

        expect($prompt->type)->toBe('todo_analysis');
    });

    it('can create company extraction prompt', function () {
        $prompt = SystemPrompt::factory()->companyExtraction()->create();

        expect($prompt->type)->toBe('company_extraction');
    });

    it('can update temperature', function () {
        $prompt = SystemPrompt::factory()->create(['temperature' => 0.5]);
        $prompt->update(['temperature' => 0.8]);

        expect($prompt->fresh()->temperature)->toBe('0.8');
    });

    it('can activate prompt', function () {
        $prompt = SystemPrompt::factory()->create(['is_active' => false]);
        $prompt->update(['is_active' => true]);

        expect($prompt->fresh()->is_active)->toBeTrue();
    });
});

describe('AiLog CRUD', function () {
    it('can create successful ai log', function () {
        $log = AiLog::factory()->successful()->create();

        expect($log->success)->toBeTrue();
        expect($log->error_message)->toBeNull();
    });

    it('can create failed ai log', function () {
        $log = AiLog::factory()->failed()->create();

        expect($log->success)->toBeFalse();
        expect($log->error_message)->not->toBeNull();
    });

    it('stores input context as json', function () {
        $log = AiLog::factory()->create();

        expect($log->input_context)->toBeArray();
    });
});

describe('AgentSession CRUD', function () {
    it('can create chat session', function () {
        $session = AgentSession::factory()->chatMode()->create();

        expect($session->mode)->toBe('chat');
        expect($session->workflow_key)->toBeNull();
    });

    it('can create workflow session', function () {
        $session = AgentSession::factory()->workflowMode('onboarding')->create();

        expect($session->mode)->toBe('workflow');
        expect($session->workflow_key)->toBe('onboarding');
    });

    it('can check if expired', function () {
        $expired = AgentSession::factory()->expired()->create();
        $active = AgentSession::factory()->create();

        expect($expired->expires_at)->toBeBefore(now());
        expect($active->expires_at)->toBeAfter(now());
    });
});
