<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebhookPreset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookPresetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_a_webhook_preset()
    {
        $preset = WebhookPreset::create([
            'user_id' => $this->user->id,
            'name' => 'Test Webhook',
            'webhook_url' => 'https://n8n.example.com/webhook/test-123',
            'description' => 'Test webhook description',
            'is_active' => false,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('webhook_presets', [
            'id' => $preset->id,
            'user_id' => $this->user->id,
            'name' => 'Test Webhook',
            'webhook_url' => 'https://n8n.example.com/webhook/test-123',
        ]);
    }

    /** @test */
    public function it_can_read_webhook_presets()
    {
        $preset = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Production Webhook',
        ]);

        $found = WebhookPreset::find($preset->id);

        $this->assertNotNull($found);
        $this->assertEquals('Production Webhook', $found->name);
        $this->assertEquals($this->user->id, $found->user_id);
    }

    /** @test */
    public function it_can_update_a_webhook_preset()
    {
        $preset = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
            'webhook_url' => 'https://old.example.com/webhook',
        ]);

        $preset->update([
            'name' => 'Updated Name',
            'webhook_url' => 'https://new.example.com/webhook',
            'description' => 'Updated description',
        ]);

        $this->assertDatabaseHas('webhook_presets', [
            'id' => $preset->id,
            'name' => 'Updated Name',
            'webhook_url' => 'https://new.example.com/webhook',
            'description' => 'Updated description',
        ]);

        $this->assertDatabaseMissing('webhook_presets', [
            'id' => $preset->id,
            'name' => 'Old Name',
        ]);
    }

    /** @test */
    public function it_can_delete_a_webhook_preset()
    {
        $preset = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $presetId = $preset->id;
        $preset->delete();

        $this->assertDatabaseMissing('webhook_presets', [
            'id' => $presetId,
        ]);

        $this->assertNull(WebhookPreset::find($presetId));
    }

    /** @test */
    public function it_activates_webhook_and_deactivates_others()
    {
        $preset1 = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Webhook 1',
            'webhook_url' => 'https://webhook1.example.com',
            'is_active' => true,
        ]);

        $preset2 = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Webhook 2',
            'webhook_url' => 'https://webhook2.example.com',
            'is_active' => false,
        ]);

        // Activate preset2
        $preset2->activate();

        $this->assertDatabaseHas('webhook_presets', [
            'id' => $preset2->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('webhook_presets', [
            'id' => $preset1->id,
            'is_active' => false,
        ]);

        // Check user's webhook URL was updated
        $this->assertEquals('https://webhook2.example.com', $this->user->fresh()->n8n_webhook_url);
    }

    /** @test */
    public function it_cascades_delete_when_user_is_deleted()
    {
        $preset = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $presetId = $preset->id;
        $this->user->delete();

        $this->assertDatabaseMissing('webhook_presets', [
            'id' => $presetId,
        ]);
    }

    /** @test */
    public function user_can_have_multiple_presets()
    {
        WebhookPreset::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertCount(3, $this->user->webhookPresets);
    }

    /** @test */
    public function only_one_preset_can_be_active_per_user()
    {
        $preset1 = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $preset2 = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $preset3 = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        // Activate preset2
        $preset2->activate();

        // Check only preset2 is active
        $activePresets = WebhookPreset::where('user_id', $this->user->id)
            ->where('is_active', true)
            ->get();

        $this->assertCount(1, $activePresets);
        $this->assertEquals($preset2->id, $activePresets->first()->id);
    }

    /** @test */
    public function it_can_list_all_presets_for_a_user()
    {
        $otherUser = User::factory()->create();

        WebhookPreset::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        WebhookPreset::factory()->count(2)->create([
            'user_id' => $otherUser->id,
        ]);

        $userPresets = WebhookPreset::where('user_id', $this->user->id)->get();

        $this->assertCount(3, $userPresets);
    }

    /** @test */
    public function it_can_filter_active_presets()
    {
        WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $activePresets = WebhookPreset::where('user_id', $this->user->id)
            ->where('is_active', true)
            ->get();

        $this->assertCount(1, $activePresets);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        WebhookPreset::create([
            'user_id' => $this->user->id,
            // Missing required 'name' and 'webhook_url'
        ]);
    }

    /** @test */
    public function it_stores_description_as_text()
    {
        $longDescription = str_repeat('This is a very long description. ', 100);

        $preset = WebhookPreset::factory()->create([
            'user_id' => $this->user->id,
            'description' => $longDescription,
        ]);

        $this->assertEquals($longDescription, $preset->fresh()->description);
    }
}
