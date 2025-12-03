<?php

use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\Run;
use App\Models\Todo;
use App\Models\MissingTodo;
use App\Models\WebhookPreset;
use App\Models\AgentSession;
use App\Models\Company;
use App\Models\User;

test('goals can be soft deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $goal = Goal::factory()->create(['company_id' => $company->id]);

    $goal->delete();

    expect($goal->trashed())->toBeTrue();
    expect(Goal::count())->toBe(0);
    expect(Goal::withTrashed()->count())->toBe(1);
});

test('soft deleted goals can be restored', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $goal = Goal::factory()->create(['company_id' => $company->id]);

    $goal->delete();
    $goal->restore();

    expect($goal->trashed())->toBeFalse();
    expect(Goal::count())->toBe(1);
});

test('goal_kpis can be soft deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $goal = Goal::factory()->create(['company_id' => $company->id]);
    $kpi = GoalKpi::factory()->create(['goal_id' => $goal->id, 'company_id' => $company->id]);

    $kpi->delete();

    expect($kpi->trashed())->toBeTrue();
    expect(GoalKpi::count())->toBe(0);
    expect(GoalKpi::withTrashed()->count())->toBe(1);
});

test('runs can be soft deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $run = Run::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $run->delete();

    expect($run->trashed())->toBeTrue();
    expect(Run::count())->toBe(0);
    expect(Run::withTrashed()->count())->toBe(1);
});

test('soft deleted runs can be restored with todos', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $run = Run::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    $todo = Todo::factory()->create(['run_id' => $run->id]);

    $run->delete();
    expect($run->trashed())->toBeTrue();

    $run->restore();
    expect($run->trashed())->toBeFalse();
    expect(Run::count())->toBe(1);
});

test('todos can be soft deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $run = Run::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    $todo = Todo::factory()->create(['run_id' => $run->id]);

    $todo->delete();

    expect($todo->trashed())->toBeTrue();
    expect(Todo::count())->toBe(0);
    expect(Todo::withTrashed()->count())->toBe(1);
});

test('missing_todos can be soft deleted', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $run = Run::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    $missingTodo = MissingTodo::factory()->create(['run_id' => $run->id]);

    $missingTodo->delete();

    expect($missingTodo->trashed())->toBeTrue();
    expect(MissingTodo::count())->toBe(0);
    expect(MissingTodo::withTrashed()->count())->toBe(1);
});

test('webhook_presets can be soft deleted', function () {
    $user = User::factory()->create();
    $preset = WebhookPreset::factory()->create(['user_id' => $user->id]);

    $preset->delete();

    expect($preset->trashed())->toBeTrue();
    expect(WebhookPreset::count())->toBe(0);
    expect(WebhookPreset::withTrashed()->count())->toBe(1);
});

test('agent_sessions can be soft deleted', function () {
    $user = User::factory()->create();
    $session = AgentSession::factory()->create(['user_id' => $user->id]);

    $session->delete();

    expect($session->trashed())->toBeTrue();
    expect(AgentSession::count())->toBe(0);
    expect(AgentSession::withTrashed()->count())->toBe(1);
});

test('force delete permanently removes soft deleted models', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $goal = Goal::factory()->create(['company_id' => $company->id]);

    $goal->delete();
    expect(Goal::withTrashed()->count())->toBe(1);

    $goal->forceDelete();
    expect(Goal::withTrashed()->count())->toBe(0);
});

test('onlyTrashed query scope works correctly', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_user_id' => $user->id]);
    $goal1 = Goal::factory()->create(['company_id' => $company->id]);
    $goal2 = Goal::factory()->create(['company_id' => $company->id]);

    $goal1->delete();

    expect(Goal::count())->toBe(1);
    expect(Goal::onlyTrashed()->count())->toBe(1);
    expect(Goal::withTrashed()->count())->toBe(2);
});
