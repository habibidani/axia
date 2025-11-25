<?php

use App\Models\SystemPrompt;
use App\Models\User;
use App\Models\Company;
use Livewire\Livewire;
use App\Livewire\AdminPrompts;

beforeEach(function () {
    // Seed system prompts
    $this->artisan('db:seed', ['--class' => 'SystemPromptsSeeder']);
});

test('guest users cannot access admin prompts page', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_guest' => true,
        'company_id' => $company->id
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(AdminPrompts::class)
        ->assertRedirect(); // Should redirect or abort
});

test('users without company cannot access admin prompts', function () {
    $user = User::factory()->create([
        'is_guest' => false,
        'company_id' => null
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(AdminPrompts::class)
        ->assertRedirect(); // Should redirect or abort
});

test('system default prompts cannot be deleted', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_guest' => false,
        'company_id' => $company->id
    ]);
    
    $this->actingAs($user);
    
    $systemPrompt = SystemPrompt::where('is_system_default', true)->first();
    
    Livewire::test(AdminPrompts::class)
        ->call('deletePrompt', $systemPrompt->id)
        ->assertSessionHas('error', 'Cannot delete system default prompts. These are required for AXIA to function properly.');
    
    // Verify prompt still exists
    expect(SystemPrompt::find($systemPrompt->id))->not->toBeNull();
});

test('system default prompts cannot be edited', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_guest' => false,
        'company_id' => $company->id
    ]);
    
    $this->actingAs($user);
    
    $systemPrompt = SystemPrompt::where('is_system_default', true)->first();
    
    Livewire::test(AdminPrompts::class)
        ->call('editPrompt', $systemPrompt->id)
        ->set('form.prompt', 'MODIFIED SYSTEM PROMPT')
        ->call('save')
        ->assertSessionHas('error', 'Cannot edit system default prompts. Clone this prompt to create a custom version.');
    
    // Verify prompt was not modified
    $systemPrompt->refresh();
    expect($systemPrompt->prompt)->not->toBe('MODIFIED SYSTEM PROMPT');
});

test('custom prompts can be deleted by authorized users', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_guest' => false,
        'company_id' => $company->id
    ]);
    
    $this->actingAs($user);
    
    // Create custom prompt
    $customPrompt = SystemPrompt::create([
        'type' => 'custom_test',
        'version' => 'v1.0',
        'prompt' => 'Custom test prompt',
        'is_active' => true,
        'is_system_default' => false,
        'temperature' => 0.7
    ]);
    
    Livewire::test(AdminPrompts::class)
        ->call('deletePrompt', $customPrompt->id)
        ->assertSessionHas('success'); // Should succeed
    
    // Verify prompt was deleted
    expect(SystemPrompt::find($customPrompt->id))->toBeNull();
});

test('custom prompts can be edited by authorized users', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_guest' => false,
        'company_id' => $company->id
    ]);
    
    $this->actingAs($user);
    
    // Create custom prompt
    $customPrompt = SystemPrompt::create([
        'type' => 'custom_test',
        'version' => 'v1.0',
        'prompt' => 'Original custom prompt',
        'is_active' => true,
        'is_system_default' => false,
        'temperature' => 0.7
    ]);
    
    Livewire::test(AdminPrompts::class)
        ->call('editPrompt', $customPrompt->id)
        ->set('form.prompt', 'Modified custom prompt')
        ->call('save')
        ->assertSessionHas('success');
    
    // Verify prompt was modified
    $customPrompt->refresh();
    expect($customPrompt->prompt)->toBe('Modified custom prompt');
});

test('cloning system default creates editable custom prompt', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_guest' => false,
        'company_id' => $company->id
    ]);
    
    $this->actingAs($user);
    
    $systemPrompt = SystemPrompt::where('is_system_default', true)->first();
    $originalCount = SystemPrompt::count();
    
    Livewire::test(AdminPrompts::class)
        ->call('clonePrompt', $systemPrompt->id)
        ->assertSessionHas('success');
    
    // Verify new prompt was created
    expect(SystemPrompt::count())->toBe($originalCount + 1);
    
    // Verify clone is NOT a system default
    $clone = SystemPrompt::latest()->first();
    expect($clone->is_system_default)->toBeFalse();
    expect($clone->version)->toContain('copy');
});

test('restore command can recover deleted system prompts', function () {
    // Delete a system prompt directly (bypass Livewire protection)
    $systemPrompt = SystemPrompt::where('is_system_default', true)->first();
    $systemPrompt->delete();
    
    // Verify deletion
    expect(SystemPrompt::where('is_system_default', true)->count())->toBe(2);
    
    // Run restore command with --force flag to skip confirmation
    $this->artisan('system:restore-prompts', ['--force' => true])
        ->expectsOutput('🔧 Running SystemPromptsSeeder...')
        ->assertSuccessful();
    
    // Verify restoration
    expect(SystemPrompt::where('is_system_default', true)->count())->toBe(3);
});
