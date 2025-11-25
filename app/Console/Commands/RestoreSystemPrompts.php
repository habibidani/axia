<?php

namespace App\Console\Commands;

use App\Models\SystemPrompt;
use Illuminate\Console\Command;
use Database\Seeders\SystemPromptsSeeder;

class RestoreSystemPrompts extends Command
{
    protected $signature = 'system:restore-prompts 
                            {--force : Force restore even if prompts exist}
                            {--type= : Only restore specific type (todo_analysis, company_extraction, goals_extraction)}';

    protected $description = 'Restore system default prompts from seeder (protection against accidental deletion)';

    public function handle(): int
    {
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info('🔄 Checking system prompts...');

        // Check if system defaults exist
        $existingDefaults = SystemPrompt::where('is_system_default', true);
        
        if ($type) {
            $existingDefaults->where('type', $type);
        }
        
        $count = $existingDefaults->count();

        if ($count > 0 && !$force) {
            $this->info("✅ System default prompts found: {$count}");
            
            if ($this->confirm('Re-run seeder to ensure latest versions?', true)) {
                $this->call('db:seed', ['--class' => SystemPromptsSeeder::class]);
                $this->info('✅ System prompts refreshed!');
            }
            
            return self::SUCCESS;
        }

        // Missing system defaults - restore
        $this->warn('⚠️  System default prompts missing or --force specified!');
        
        if (!$force && !$this->confirm('Run SystemPromptsSeeder to restore?', true)) {
            $this->error('Aborted.');
            return self::FAILURE;
        }

        $this->info('🔧 Running SystemPromptsSeeder...');
        $this->call('db:seed', ['--class' => SystemPromptsSeeder::class]);

        // Verify restoration
        $restoredCount = SystemPrompt::where('is_system_default', true)->count();
        
        if ($restoredCount > 0) {
            $this->info("✅ Successfully restored {$restoredCount} system default prompts!");
            
            // Show restored prompts
            $restored = SystemPrompt::where('is_system_default', true)->get();
            $this->table(
                ['Type', 'Version', 'Active', 'Created'],
                $restored->map(fn($p) => [
                    $p->type,
                    $p->version,
                    $p->is_active ? '✓' : '✗',
                    $p->created_at->format('Y-m-d H:i')
                ])
            );
            
            return self::SUCCESS;
        }

        $this->error('❌ Failed to restore system prompts!');
        return self::FAILURE;
    }
}
