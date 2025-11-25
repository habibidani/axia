<?php

use App\Models\{User, Company, Goal, GoalKpi, Run, Todo, TodoEvaluation, MissingTodo, AgentSession, WebhookPreset};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Relationships', function () {
    it('has one company (1:1 intended)', function () {
        $user = User::factory()->create();
        $company = Company::factory()->forUser($user)->create();

        expect($user->fresh()->company)->not->toBeNull();
        expect($user->fresh()->company->id)->toBe($company->id);
    });

    it('has many runs', function () {
        $user = User::factory()->create();
        $runs = Run::factory()->count(3)->forUser($user)->create();

        expect(Run::where('user_id', $user->id)->count())->toBe(3);
    });

    it('has many agent sessions', function () {
        $user = User::factory()->create();
        AgentSession::factory()->count(2)->forUser($user)->create();

        expect(AgentSession::where('user_id', $user->id)->count())->toBe(2);
    });

    it('has many webhook presets', function () {
        $user = User::factory()->create();
        WebhookPreset::factory()->count(3)->forUser($user)->create();

        expect($user->fresh()->webhookPresets)->toHaveCount(3);
    });

    it('cascades delete to webhook presets', function () {
        $user = User::factory()->create();
        WebhookPreset::factory()->count(2)->forUser($user)->create();

        expect(WebhookPreset::count())->toBe(2);

        $user->delete();

        expect(WebhookPreset::count())->toBe(0);
    });
});

describe('Company Relationships', function () {
    it('belongs to owner user', function () {
        $user = User::factory()->create();
        $company = Company::factory()->forUser($user)->create();

        expect($company->fresh()->owner->id)->toBe($user->id);
    });

    it('has many goals', function () {
        $company = Company::factory()->create();
        Goal::factory()->count(5)->forCompany($company)->create();

        expect($company->fresh()->goals)->toHaveCount(5);
    });

    it('has many runs', function () {
        $company = Company::factory()->create();
        Run::factory()->count(3)->forCompany($company)->create();

        expect($company->fresh()->runs)->toHaveCount(3);
    });

    it('has many standalone kpis', function () {
        $company = Company::factory()->create();
        GoalKpi::factory()->count(2)->standalone()->state(['company_id' => $company->id])->create();

        expect($company->fresh()->kpis)->toHaveCount(2);
    });
});

describe('Goal Relationships', function () {
    it('belongs to company', function () {
        $company = Company::factory()->create();
        $goal = Goal::factory()->forCompany($company)->create();

        expect($goal->fresh()->company->id)->toBe($company->id);
    });

    it('has many kpis', function () {
        $goal = Goal::factory()->create();
        GoalKpi::factory()->count(3)->forGoal($goal)->create();

        expect($goal->fresh()->kpis)->toHaveCount(3);
    });

    it('has many todo evaluations as primary goal', function () {
        $goal = Goal::factory()->create();
        TodoEvaluation::factory()->count(2)->state(['primary_goal_id' => $goal->id])->create();

        expect($goal->fresh()->todoEvaluations)->toHaveCount(2);
    });

    it('has many missing todos', function () {
        $goal = Goal::factory()->create();
        MissingTodo::factory()->count(3)->forGoal($goal)->create();

        expect($goal->fresh()->missingTodos)->toHaveCount(3);
    });
});

describe('GoalKpi Relationships', function () {
    it('belongs to goal', function () {
        $goal = Goal::factory()->create();
        $kpi = GoalKpi::factory()->forGoal($goal)->create();

        expect($kpi->fresh()->goal->id)->toBe($goal->id);
    });

    it('can be standalone (no goal)', function () {
        $company = Company::factory()->create();
        $kpi = GoalKpi::factory()->standalone()->state(['company_id' => $company->id])->create();

        expect($kpi->fresh()->goal)->toBeNull();
        expect($kpi->fresh()->company->id)->toBe($company->id);
    });

    it('has many todo evaluations as primary kpi', function () {
        $kpi = GoalKpi::factory()->create();
        TodoEvaluation::factory()->count(2)->state(['primary_kpi_id' => $kpi->id])->create();

        expect($kpi->fresh()->todoEvaluations)->toHaveCount(2);
    });

    it('has many missing todos', function () {
        $kpi = GoalKpi::factory()->create();
        MissingTodo::factory()->count(2)->forKpi($kpi)->create();

        expect($kpi->fresh()->missingTodos)->toHaveCount(2);
    });
});

describe('Run Relationships', function () {
    it('belongs to company', function () {
        $company = Company::factory()->create();
        $run = Run::factory()->forCompany($company)->create();

        expect($run->fresh()->company->id)->toBe($company->id);
    });

    it('belongs to user', function () {
        $user = User::factory()->create();
        $run = Run::factory()->forUser($user)->create();

        expect($run->fresh()->user->id)->toBe($user->id);
    });

    it('has many todos', function () {
        $run = Run::factory()->create();
        Todo::factory()->count(10)->forRun($run)->create();

        expect($run->fresh()->todos)->toHaveCount(10);
    });

    it('has many todo evaluations', function () {
        $run = Run::factory()->create();
        TodoEvaluation::factory()->count(5)->forRun($run)->create();

        expect($run->fresh()->todoEvaluations)->toHaveCount(5);
    });

    it('has many missing todos', function () {
        $run = Run::factory()->create();
        MissingTodo::factory()->count(4)->forRun($run)->create();

        expect($run->fresh()->missingTodos)->toHaveCount(4);
    });

    it('has many ai logs', function () {
        $run = Run::factory()->create();
        \App\Models\AiLog::factory()->count(3)->forRun($run)->create();

        expect($run->fresh()->aiLogs)->toHaveCount(3);
    });
});

describe('Todo Relationships', function () {
    it('belongs to run', function () {
        $run = Run::factory()->create();
        $todo = Todo::factory()->forRun($run)->create();

        expect($todo->fresh()->run->id)->toBe($run->id);
    });

    it('has one evaluation', function () {
        $todo = Todo::factory()->create();
        TodoEvaluation::factory()->forTodo($todo)->create();

        expect($todo->fresh()->evaluation)->not->toBeNull();
    });
});

describe('Cascade Deletes', function () {
    it('deletes company goals when company is deleted', function () {
        $company = Company::factory()->create();
        Goal::factory()->count(3)->forCompany($company)->create();

        expect(Goal::count())->toBe(3);

        $company->delete();

        expect(Goal::count())->toBe(0);
    });

    it('deletes goal kpis when goal is deleted', function () {
        $goal = Goal::factory()->create();
        GoalKpi::factory()->count(2)->forGoal($goal)->create();

        expect(GoalKpi::where('goal_id', $goal->id)->count())->toBe(2);

        $goal->delete();

        expect(GoalKpi::where('goal_id', $goal->id)->count())->toBe(0);
    });

    it('deletes run todos when run is deleted', function () {
        $run = Run::factory()->create();
        Todo::factory()->count(5)->forRun($run)->create();

        expect(Todo::count())->toBe(5);

        $run->delete();

        expect(Todo::count())->toBe(0);
    });
});
