<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\WebhookPreset;
use App\Models\Run;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookPresetVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_version_with_incremented_number()
    {
        $user = User::factory()->create();

        $preset = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'My Webhook',
            'webhook_url' => 'https://example.com/v1',
            'config_json' => ['key' => 'value1'],
            'version' => 1,
        ]);

        $newVersion = $preset->createNewVersion([
            'webhook_url' => 'https://example.com/v2',
            'config_json' => ['key' => 'value2'],
        ]);

        $this->assertEquals(2, $newVersion->version);
        $this->assertEquals('https://example.com/v2', $newVersion->webhook_url);
        $this->assertEquals(['key' => 'value2'], $newVersion->config_json);
        $this->assertFalse($newVersion->is_active);
    }

    public function test_links_version_to_run_when_created()
    {
        $user = User::factory()->create();
        $run = Run::factory()->create();

        $preset = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'My Webhook',
            'webhook_url' => 'https://example.com/v1',
            'version' => 1,
        ]);

        $newVersion = $preset->createNewVersion([
            'webhook_url' => 'https://example.com/v2',
        ], $run->id);

        $this->assertEquals($run->id, $newVersion->created_by_run_id);
        $this->assertInstanceOf(Run::class, $newVersion->createdByRun);
    }

    public function test_rollback_creates_new_version_with_old_config()
    {
        $user = User::factory()->create();

        $v1 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'My Webhook',
            'webhook_url' => 'https://example.com/v1',
            'config_json' => ['setting' => 'old'],
            'version' => 1,
        ]);

        $v2 = $v1->createNewVersion([
            'webhook_url' => 'https://example.com/v2',
            'config_json' => ['setting' => 'new'],
        ]);

        $rollback = $v2->rollbackTo($v1);

        $this->assertEquals(3, $rollback->version);
        $this->assertEquals('https://example.com/v1', $rollback->webhook_url);
        $this->assertEquals(['setting' => 'old'], $rollback->config_json);
        $this->assertEquals($v1->id, $rollback->rollback_to_version_id);
    }

    public function test_get_version_history_returns_all_versions()
    {
        $user = User::factory()->create();

        $v1 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'My Webhook',
            'webhook_url' => 'https://example.com/v1',
            'version' => 1,
        ]);

        $v2 = $v1->createNewVersion(['webhook_url' => 'https://example.com/v2']);
        $v3 = $v2->createNewVersion(['webhook_url' => 'https://example.com/v3']);

        $history = $v1->getVersionHistory();

        $this->assertCount(3, $history);
        $this->assertEquals(3, $history->first()->version);
        $this->assertEquals(1, $history->last()->version);
    }

    public function test_only_one_active_preset_per_user()
    {
        $user = User::factory()->create();

        $preset1 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'Webhook 1',
            'webhook_url' => 'https://example.com/1',
            'version' => 1,
            'is_active' => true,
        ]);

        $preset2 = WebhookPreset::create([
            'user_id' => $user->id,
            'name' => 'Webhook 2',
            'webhook_url' => 'https://example.com/2',
            'version' => 1,
        ]);

        $preset2->activate();

        $preset1->refresh();
        $preset2->refresh();

        $this->assertFalse($preset1->is_active);
        $this->assertTrue($preset2->is_active);
    }
}
