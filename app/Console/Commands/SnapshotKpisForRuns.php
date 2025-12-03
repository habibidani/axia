<?php

namespace App\Console\Commands;

use App\Models\GoalKpi;
use App\Models\KpiSnapshot;
use App\Models\Run;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SnapshotKpisForRuns extends Command
{
    protected $signature = 'kpi:snapshot {--runId=} {--limit=50}';
    protected $description = 'Create KPI snapshots for recent runs or a specific run';

    public function handle(): int
    {
        $runId = $this->option('runId');
        $limit = (int)($this->option('limit') ?? 50);

        $query = Run::query()->orderByDesc('created_at');
        if ($runId) {
            $query->where('id', $runId);
        } else {
            $query->limit($limit);
        }

        $runs = $query->get();
        if ($runs->isEmpty()) {
            $this->info('No runs found to snapshot.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($runs) {
            foreach ($runs as $run) {
                // Fetch relevant KPIs for the run via company relation
                $company = $run->company;
                if (!$company) {
                    continue;
                }

                $kpis = GoalKpi::query()
                    ->where('company_id', $company->id)
                    ->where('is_top_kpi', true)
                    ->get();

                foreach ($kpis as $kpi) {
                    KpiSnapshot::create([
                        'id' => (string) Str::uuid(),
                        'run_id' => $run->id,
                        'goal_kpi_id' => $kpi->id,
                        'current_value' => $kpi->current_value,
                        'target_value' => $kpi->target_value,
                        'unit' => $kpi->unit,
                        'is_top_kpi' => (bool) $kpi->is_top_kpi,
                        'created_at' => now(),
                    ]);
                }
            }
        });

        $this->info('KPI snapshots created successfully.');
        return self::SUCCESS;
    }
}
