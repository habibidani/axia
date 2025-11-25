# Migration Optimization Plan

## Current State Analysis

### ✅ Good Migrations (Keep as-is)
1. `0001_01_01_000000_create_users_table.php` - Core users table
2. `0001_01_01_000001_create_cache_table.php` - Cache infrastructure
3. `0001_01_01_000002_create_jobs_table.php` - Queue jobs
4. `2024_11_18_000001_create_companies_table.php` - Companies
5. `2024_11_18_000002_create_goals_table.php` - Goals
6. `2024_11_18_000003_create_goal_kpis_table.php` - KPIs
7. `2024_11_18_000004_create_runs_table.php` - Runs
8. `2024_11_18_000005_create_todos_table.php` - Todos
9. `2024_11_18_000006_create_todo_evaluations_table.php` - Evaluations
10. `2024_11_18_000007_create_missing_todos_table.php` - Missing todos

### 🔄 Migrations to Consolidate
These can be merged into their parent tables:

1. `2025_11_18_142121_make_goal_id_nullable_in_goal_kpis_table.php`
   → Merge into `2024_11_18_000003_create_goal_kpis_table.php`

2. `2025_11_18_164217_add_smart_text_fields_to_companies_goals_and_goal_kpis.php`
   → Merge into respective table creation migrations

3. `2025_11_20_183330_add_webhook_config_to_users_table.php`
   → Merge into `0001_01_01_000000_create_users_table.php`

### ✅ Keep Separate (New Features)
1. `2025_11_18_144226_create_system_prompts_table.php` - AI prompts system
2. `2025_11_18_144227_create_ai_logs_table.php` - AI logging
3. `2025_11_19_172700_create_personal_access_tokens_table.php` - API tokens
4. `2025_11_20_021520_create_agent_sessions_table.php` - AI agent sessions
5. `2025_11_20_202446_create_webhook_presets_table.php` - Webhook management

## Optimization Actions

### Step 1: Create New Consolidated Migrations
Create clean versions of the base tables with all modifications included:

- `database/migrations/clean/2024_11_18_000001_create_companies_table.php` (with smart_text)
- `database/migrations/clean/2024_11_18_000002_create_goals_table.php` (with smart_text)
- `database/migrations/clean/2024_11_18_000003_create_goal_kpis_table.php` (with nullable goal_id + smart_text)
- `database/migrations/clean/0001_01_01_000000_create_users_table.php` (with webhook fields)

### Step 2: Migration Squashing Strategy

**Option A: For Fresh Installs** (Recommended for new environments)
- Use the clean migrations from Step 1
- Skip all the "add column" migrations

**Option B: For Existing Databases**
- Keep all current migrations as-is
- They will continue to work for existing installations
- New deployments can use squashed versions

### Step 3: Testing Plan

1. **Test Fresh Migration** (Empty Database)
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Test Rollback Capability**
   ```bash
   php artisan migrate:rollback --step=5
   php artisan migrate
   ```

3. **Test Data Integrity**
   - Run CRUD tests for all models
   - Verify foreign key constraints
   - Check cascade deletes

## Webhook CRUD Requirements ✅

All CRUD operations are supported via:

### Model Methods
- ✅ **Create**: `WebhookPreset::create([...])`
- ✅ **Read**: `WebhookPreset::find($id)`, `WebhookPreset::where(...)->get()`
- ✅ **Update**: `$preset->update([...])`
- ✅ **Delete**: `$preset->delete()`

### Additional Features
- ✅ **Activate/Deactivate**: `$preset->activate()`
- ✅ **Cascading Delete**: When user deleted, presets auto-delete
- ✅ **User Relation**: `$user->webhookPresets`
- ✅ **Active Preset Constraint**: Only one active per user

### Test Coverage
- ✅ Full CRUD test suite in `tests/Feature/WebhookPresetTest.php`
- ✅ Factory for data generation
- ✅ Relationship tests
- ✅ Constraint validation

## Execution Order

1. Run webhook CRUD tests: `./test-webhook-crud.ps1`
2. Review test results
3. If tests pass, migrations are solid
4. Optionally: Create squashed versions for clean deployments

## Database Schema Validation

### Foreign Keys Check
```sql
-- All foreign keys should be properly defined
SELECT tc.table_name, kcu.column_name, 
       ccu.table_name AS foreign_table_name,
       ccu.column_name AS foreign_column_name 
FROM information_schema.table_constraints AS tc 
JOIN information_schema.key_column_usage AS kcu
  ON tc.constraint_name = kcu.constraint_name
JOIN information_schema.constraint_column_usage AS ccu
  ON ccu.constraint_name = tc.constraint_name
WHERE tc.constraint_type = 'FOREIGN KEY';
```

### Index Check
```sql
-- Verify all necessary indexes exist
SELECT tablename, indexname, indexdef 
FROM pg_indexes 
WHERE schemaname = 'public' 
ORDER BY tablename, indexname;
```
