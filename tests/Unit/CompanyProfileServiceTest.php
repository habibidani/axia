<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\CompanyProfile;
use App\Services\CompanyProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_user_field_when_present()
    {
        $company = Company::factory()->create([
            'user_description' => 'User-provided description',
        ]);

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_user_input',
            'raw_text' => 'AI generated text',
        ]);

        $service = new CompanyProfileService();
        $data = $service->getCompanyProfileData($company->id, 'customer_profile');

        $this->assertEquals('user', $data['source']);
        $this->assertEquals('User-provided description', $data['raw_text']);
    }

    public function test_returns_ai_from_user_input_when_no_user_field()
    {
        $company = Company::factory()->create();

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_user_input',
            'raw_text' => 'AI from user input',
        ]);

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'customer_profile',
            'source_type' => 'ai_from_domain',
            'raw_text' => 'AI from domain',
        ]);

        $service = new CompanyProfileService();
        $data = $service->getCompanyProfileData($company->id, 'customer_profile');

        $this->assertEquals('ai_from_user_input', $data['source']);
        $this->assertEquals('AI from user input', $data['raw_text']);
    }

    public function test_returns_ai_from_domain_when_no_user_input()
    {
        $company = Company::factory()->create();

        CompanyProfile::create([
            'company_id' => $company->id,
            'profile_type' => 'market_insights',
            'source_type' => 'ai_from_domain',
            'raw_text' => 'Domain insights',
        ]);

        $service = new CompanyProfileService();
        $data = $service->getCompanyProfileData($company->id, 'market_insights');

        $this->assertEquals('ai_from_domain', $data['source']);
        $this->assertEquals('Domain insights', $data['raw_text']);
    }

    public function test_returns_null_when_no_data()
    {
        $company = Company::factory()->create();

        $service = new CompanyProfileService();
        $data = $service->getCompanyProfileData($company->id, 'customer_profile');

        $this->assertNull($data);
    }

    public function test_returns_null_for_nonexistent_company()
    {
        $service = new CompanyProfileService();
        $data = $service->getCompanyProfileData('00000000-0000-0000-0000-000000000000', 'customer_profile');

        $this->assertNull($data);
    }
}
