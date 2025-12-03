<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\CompanyProfile;

class BackfillCompanyProfiles extends Command
{
    protected $signature = 'company:backfill-profiles {--limit=0 : Limit number of companies}';
    protected $description = 'Backfill AI-generated fields from companies into company_profiles with source types';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        Company::query()
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->chunk(100, function ($companies) {
                foreach ($companies as $company) {
                    $this->backfillForCompany($company);
                }
            });

        $this->info('Backfill complete.');
        return self::SUCCESS;
    }

    protected function backfillForCompany(Company $company): void
    {
        $map = [
            'customer_profile' => ['fields' => ['customer_profile', 'original_smart_text'], 'source' => 'ai_from_user_input'],
            'market_insights' => ['fields' => ['market_insights'], 'source' => 'ai_from_domain'],
            'positioning' => ['fields' => ['positioning'], 'source' => 'ai_mixed'],
            'competitive_analysis' => ['fields' => ['competitive_analysis'], 'source' => 'ai_mixed'],
        ];

        foreach ($map as $profileType => $conf) {
            foreach ($conf['fields'] as $field) {
                if (!isset($company->{$field}) || empty($company->{$field})) {
                    continue;
                }

                CompanyProfile::create([
                    'company_id' => $company->id,
                    'profile_type' => $profileType,
                    'source_type' => $conf['source'],
                    'raw_text' => is_string($company->{$field}) ? $company->{$field} : null,
                    'ai_extracted_json' => is_array($company->{$field}) ? $company->{$field} : null,
                ]);
            }
        }
    }
}
