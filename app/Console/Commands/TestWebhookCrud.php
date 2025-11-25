<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WebhookPreset;
use Illuminate\Console\Command;

class TestWebhookCrud extends Command
{
    protected $signature = 'test:webhook-crud';
    protected $description = 'Test all CRUD operations for Webhook Presets';

    public function handle(): int
    {
        $this->info('🧪 Testing Webhook Preset CRUD Operations');
        $this->newLine();

        // Create test user
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'webhook-test@example.com',
        ]);
        $this->info("✅ Created test user: {$user->email}");

        // TEST 1: CREATE
        $this->info('1️⃣  Testing CREATE...');
        $preset1 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'Production Webhook',
            'webhook_url' => 'https://n8n.example.com/webhook/prod-123',
            'description' => 'Production environment webhook',
            'is_active' => false,
            'is_default' => false,
        ]);
        $this->line("   Created preset: {$preset1->name} (ID: {$preset1->id})");

        // TEST 2: READ
        $this->info('2️⃣  Testing READ...');
        $found = WebhookPreset::find($preset1->id);
        if ($found && $found->name === 'Production Webhook') {
            $this->line("   ✓ Successfully read preset: {$found->name}");
        } else {
            $this->error('   ✗ Failed to read preset');
            return 1;
        }

        // TEST 3: UPDATE
        $this->info('3️⃣  Testing UPDATE...');
        $preset1->update([
            'name' => 'Production Webhook Updated',
            'description' => 'Updated description',
        ]);
        $updated = WebhookPreset::find($preset1->id);
        if ($updated->name === 'Production Webhook Updated') {
            $this->line("   ✓ Successfully updated preset: {$updated->name}");
        } else {
            $this->error('   ✗ Failed to update preset');
            return 1;
        }

        // TEST 4: LIST (Multiple presets)
        $this->info('4️⃣  Testing LIST...');
        $preset2 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'Development Webhook',
            'webhook_url' => 'https://n8n.example.com/webhook/dev-456',
            'is_active' => false,
        ]);
        $preset3 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'Staging Webhook',
            'webhook_url' => 'https://n8n.example.com/webhook/staging-789',
            'is_active' => false,
        ]);

        $allPresets = WebhookPreset::where('user_id', $user->id)->get();
        if ($allPresets->count() === 3) {
            $this->line("   ✓ Successfully listed {$allPresets->count()} presets");
            foreach ($allPresets as $preset) {
                $this->line("      - {$preset->name}");
            }
        } else {
            $this->error("   ✗ Expected 3 presets, found {$allPresets->count()}");
            return 1;
        }

        // TEST 5: ACTIVATE (Only one active)
        $this->info('5️⃣  Testing ACTIVATE...');
        $preset2->activate();
        
        $activePresets = WebhookPreset::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        if ($activePresets->count() === 1 && $activePresets->first()->id === $preset2->id) {
            $this->line("   ✓ Successfully activated preset: {$preset2->name}");
            $this->line("   ✓ Only one preset is active");
        } else {
            $this->error("   ✗ Failed activation test (found {$activePresets->count()} active)");
            return 1;
        }

        // Verify user's webhook URL was updated
        $user->refresh();
        if ($user->n8n_webhook_url === $preset2->webhook_url) {
            $this->line("   ✓ User's webhook URL updated to: {$user->n8n_webhook_url}");
        } else {
            $this->error("   ✗ User's webhook URL not updated");
            return 1;
        }

        // TEST 6: SWITCH ACTIVE
        $this->info('6️⃣  Testing SWITCH ACTIVE...');
        $preset3->activate();
        
        $preset2->refresh();
        $preset3->refresh();
        
        if (!$preset2->is_active && $preset3->is_active) {
            $this->line("   ✓ Successfully switched active preset");
            $this->line("      - {$preset2->name}: inactive");
            $this->line("      - {$preset3->name}: active");
        } else {
            $this->error('   ✗ Failed to switch active preset');
            return 1;
        }

        // TEST 7: DELETE
        $this->info('7️⃣  Testing DELETE...');
        $preset1Id = $preset1->id;
        $preset1->delete();
        
        $deleted = WebhookPreset::find($preset1Id);
        if ($deleted === null) {
            $this->line("   ✓ Successfully deleted preset (ID: {$preset1Id})");
        } else {
            $this->error('   ✗ Failed to delete preset');
            return 1;
        }

        $remaining = WebhookPreset::where('user_id', $user->id)->count();
        $this->line("   ✓ Remaining presets: {$remaining}");

        // TEST 8: CASCADE DELETE
        $this->info('8️⃣  Testing CASCADE DELETE...');
        $preset2Id = $preset2->id;
        $preset3Id = $preset3->id;
        
        $user->delete();
        
        $orphaned = WebhookPreset::whereIn('id', [$preset2Id, $preset3Id])->count();
        if ($orphaned === 0) {
            $this->line('   ✓ Presets cascade deleted with user');
        } else {
            $this->error("   ✗ Found {$orphaned} orphaned presets");
            return 1;
        }

        // TEST 9: RELATION TEST
        $this->info('9️⃣  Testing USER RELATION...');
        $newUser = User::factory()->create();
        WebhookPreset::factory()->count(3)->create(['user_id' => $newUser->id]);
        
        if ($newUser->webhookPresets()->count() === 3) {
            $this->line("   ✓ User relation works correctly (3 presets)");
        } else {
            $this->error('   ✗ User relation failed');
            return 1;
        }

        // Cleanup
        $newUser->delete();

        $this->newLine();
        $this->info('✅ All CRUD tests passed successfully!');
        $this->newLine();

        return 0;
    }
}
