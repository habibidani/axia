<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use App\Models\Goal;
use App\Models\GoalKpi;
use App\Models\AiExtractedMetadata;

class BackfillAiMetadata extends Command
{
    protected $signature = 'ai:backfill-metadata {--limit=0 : Limit number of records per entity type}';
    protected $description = 'Backfill AI extracted metadata from existing JSON/text fields into ai_extracted_metadata table';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info('Backfilling Users.webhook_config ...');
        User::query()
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->whereNotNull('webhook_config')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    AiExtractedMetadata::create([
                        'entity_type' => User::class,
                        'entity_id' => $user->id,
                        'raw_text' => null,
                        'extracted_data_json' => $user->webhook_config,
                    ]);
                }
            });

        $this->info('Backfilling Companies.original_smart_text ...');
        Company::query()
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->whereNotNull('original_smart_text')
            ->chunk(100, function ($companies) {
                foreach ($companies as $company) {
                    AiExtractedMetadata::create([
                        'entity_type' => Company::class,
                        'entity_id' => $company->id,
                        'raw_text' => $company->original_smart_text,
                        'extracted_data_json' => null,
                    ]);
                }
            });

        $this->info('Backfilling Goals.original_smart_text ...');
        Goal::query()
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->whereNotNull('original_smart_text')
            ->chunk(100, function ($goals) {
                foreach ($goals as $goal) {
                    AiExtractedMetadata::create([
                        'entity_type' => Goal::class,
                        'entity_id' => $goal->id,
                        'raw_text' => $goal->original_smart_text,
                        'extracted_data_json' => null,
                    ]);
                }
            });

        $this->info('Backfilling GoalKpi.original_smart_text ...');
        GoalKpi::query()
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->whereNotNull('original_smart_text')
            ->chunk(100, function ($kpis) {
                foreach ($kpis as $kpi) {
                    AiExtractedMetadata::create([
                        'entity_type' => GoalKpi::class,
                        'entity_id' => $kpi->id,
                        'raw_text' => $kpi->original_smart_text,
                        'extracted_data_json' => null,
                    ]);
                }
            });

        $this->info('Backfill complete.');
        return self::SUCCESS;
    }
}
