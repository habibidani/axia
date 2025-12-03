<?php

namespace Tests\Unit;

use App\Models\Goal;
use App\Models\Todo;
use App\Models\Company;
use App\Models\CompanyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class EnumValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_priority_enum_accepts_valid_values()
    {
        $company = Company::factory()->create();

        $goal = Goal::create([
            'company_id' => $company->id,
            'title' => 'Test Goal',
            'priority' => 'high',
        ]);

        $this->assertEquals('high', $goal->priority);
    }

    public function test_goal_priority_enum_rejects_invalid_values()
    {
        $this->expectException(QueryException::class);

        $company = Company::factory()->create();

        Goal::create([
            'company_id' => $company->id,
            'title' => 'Test Goal',
            'priority' => 'urgent', // Invalid value
        ]);
    }

    public function test_todo_source_enum_accepts_valid_values()
    {
        $run = \App\Models\Run::factory()->create();

        $todo = Todo::factory()->create([
            'run_id' => $run->id,
            'source' => 'csv',
        ]);

        $this->assertEquals('csv', $todo->source);
    }

    public function test_todo_status_enum_accepts_valid_values()
    {
        $run = \App\Models\Run::factory()->create();

        $todo = Todo::factory()->create([
            'run_id' => $run->id,
            'status' => 'in_progress',
        ]);

        $this->assertEquals('in_progress', $todo->status);
    }

    public function test_company_business_model_enum_accepts_valid_values()
    {
        $company = Company::factory()->create([
            'business_model' => 'b2b_saas',
        ]);

        $this->assertEquals('b2b_saas', $company->business_model);
    }

    public function test_company_business_model_enum_rejects_invalid_values()
    {
        $this->expectException(QueryException::class);

        Company::factory()->create([
            'business_model' => 'invalid_model',
        ]);
    }

    public function test_company_profile_enums_accept_valid_values()
    {
        $company = Company::factory()->create();

        $profile = CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_user_input',
        ]);

        $this->assertEquals('customer_profile', $profile->profile_type);
        $this->assertEquals('ai_from_user_input', $profile->source_type);
    }

    public function test_company_profile_type_enum_rejects_invalid_values()
    {
        $this->expectException(QueryException::class);

        $company = Company::factory()->create();

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'invalid_type',
            'source_type' => 'ai_from_user_input',
        ]);
    }
}
