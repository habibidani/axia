<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_profile()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/companies/{$company->id}/profiles", [
                'profile_type' => 'customer_profile',
                'source_type' => 'ai_from_user_input',
                'raw_text' => 'Test profile text',
                'ai_extracted_json' => ['key' => 'value'],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'company_id' => $company->id,
                'profile_type' => 'customer_profile',
                'source_type' => 'ai_from_user_input',
            ]);

        $this->assertDatabaseHas('company_profiles', [
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'raw_text' => 'Test profile text',
        ]);
    }

    public function test_can_list_profiles()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_user_input',
            'raw_text' => 'Profile 1',
        ]);

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'market_insights',
            'source_type' => 'ai_from_domain',
            'raw_text' => 'Profile 2',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/companies/{$company->id}/profiles?type=customer_profile");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['raw_text' => 'Profile 1']);
    }

    public function test_can_get_prioritized_data()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_description' => 'User description',
        ]);

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_user_input',
            'raw_text' => 'AI description',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/companies/{$company->id}/profile-data?type=customer_profile");

        $response->assertStatus(200)
            ->assertJson([
                'source' => 'user',
                'raw_text' => 'User description',
            ]);
    }

    public function test_validation_fails_for_invalid_profile_type()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/companies/{$company->id}/profiles", [
                'profile_type' => 'invalid_type',
                'source_type' => 'ai_from_user_input',
            ]);

        $response->assertStatus(422);
    }
}
