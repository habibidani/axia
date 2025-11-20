# Webhook-based AI Architecture

## Overview

**All AI processing in this application is now routed through n8n webhooks.** There are no direct OpenAI API calls from the Laravel application.

## Architecture

```
User Request
    ↓
Laravel Controller/Livewire Component
    ↓
WebhookAiService
    ↓
n8n Webhook (https://n8n.getaxia.de/webhook/ai-analysis)
    ↓
OpenAI API (handled by n8n)
    ↓
Response back through n8n
    ↓
Laravel processes result
    ↓
User Interface
```

## Services

### WebhookAiService (`app/Services/WebhookAiService.php`)

Replaces the old `OpenAiAnalysisService`. Routes all AI tasks to n8n webhook.

**Methods:**
- `analyzeTodos(Run $run, Collection $todos, ?Company $company)` - Analyze todos against company goals
- `extractCompanyInfo(string $text)` - Extract structured company data from freeform text
- `extractGoalsAndKpis(string $text)` - Extract goals and KPIs from freeform text

**Webhook Payload Format:**
```json
{
    "task": "todo_analysis|company_extraction|goals_extraction",
    "system_message": "System prompt from database",
    "user_prompt": "User prompt with context variables filled",
    "temperature": 0.7,
    "run_id": "uuid",
    "company_id": "uuid"
}
```

**Expected Response Format:**
```json
{
    "success": true,
    "data": {
        // Task-specific result data (JSON parsed from LLM response)
    },
    "tokens_used": 1234
}
```

Or on error:
```json
{
    "success": false,
    "error": "Error message"
}
```

## Components Using WebhookAiService

1. **`app/Livewire/Home.php`** - Todo analysis on home page
2. **`app/Livewire/Onboarding.php`** - Company and goals extraction during onboarding
3. **`app/Livewire/GoalsEdit.php`** - Goals extraction in edit mode
4. **`app/Livewire/CompanyEdit.php`** - Company info extraction
5. **`app/Livewire/PromptTester.php`** - Prompt testing tool
6. **`app/Http/Controllers/Api/TodoController.php`** - API endpoint for todo analysis

## Configuration

### Environment Variables (.env)

```bash
# n8n Webhook URLs (All AI processing via n8n)
N8N_AGENT_WEBHOOK_URL=https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d
N8N_AI_ANALYSIS_WEBHOOK_URL=https://n8n.getaxia.de/webhook/ai-analysis

# MCP Configuration
MCP_SHARED_SECRET=vJHyD57OQ8LvXbLA7d/3NaazQNhyE/bs7XnNNfpHIa0=
MCP_SERVER_URL=http://mcp-axia:8102
```

### Config File (config/services.php)

```php
'n8n' => [
    'webhook_url' => env('N8N_WEBHOOK_URL', 'http://n8n:5678'),
    'webhook_secret' => env('N8N_WEBHOOK_SECRET'),
    'chat_webhook_url' => env('N8N_CHAT_WEBHOOK_URL', 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d'),
    'agent_webhook_url' => env('N8N_AGENT_WEBHOOK_URL', 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d'),
    'ai_analysis_webhook_url' => env('N8N_AI_ANALYSIS_WEBHOOK_URL', 'https://n8n.getaxia.de/webhook/ai-analysis'),
],
```

**Note:** The old `openai` config has been removed.

## n8n Workflow Requirements

The n8n workflow at `https://n8n.getaxia.de/webhook/ai-analysis` must:

1. **Accept POST requests** with the payload format above
2. **Route to OpenAI** based on the `task` field
3. **Parse JSON responses** from OpenAI (all prompts use `response_format: json_object`)
4. **Return standardized format** with `success`, `data`, and `tokens_used` fields
5. **Handle errors** and return `success: false` with error message

### Example n8n Workflow Structure

```
Webhook Trigger
    ↓
Switch (based on task type)
    ├─→ todo_analysis → OpenAI Chat → Format Response
    ├─→ company_extraction → OpenAI Chat → Format Response
    └─→ goals_extraction → OpenAI Chat → Format Response
    ↓
Respond to Webhook
```

## Benefits

1. **No API Keys in Laravel** - OpenAI credentials only in n8n
2. **Centralized AI Logic** - All LLM interactions managed in one place (n8n)
3. **Easy Model Switching** - Change model in n8n without Laravel code changes
4. **Better Monitoring** - n8n provides execution logs and metrics
5. **Scalability** - n8n can distribute load across multiple LLM providers
6. **Cost Control** - Rate limiting and quotas managed in n8n
7. **Security** - API keys never exposed to Laravel codebase

## Testing

### Test Webhook Connection

```bash
curl -X POST https://n8n.getaxia.de/webhook/ai-analysis \
  -H "Content-Type: application/json" \
  -d '{
    "task": "company_extraction",
    "system_message": "Extract company info from text",
    "user_prompt": "We are Axia GmbH, a B2B SaaS startup with 2 founders and 3 employees.",
    "temperature": 0.7
  }'
```

Expected response:
```json
{
  "success": true,
  "data": {
    "name": "Axia GmbH",
    "business_model": "b2b_saas",
    "team_cofounders": 2,
    "team_employees": 3
  },
  "tokens_used": 234
}
```

### Test in Application

1. Log into Laravel app at `http://localhost:6478`
2. Go to Onboarding or Company Edit
3. Use "Smart Input" mode
4. Enter company description
5. Click "Extract with AI"
6. Verify extraction works via webhook

## Removed Files

- ✅ `app/Services/OpenAiAnalysisService.php` - Deleted (replaced by WebhookAiService)
- ✅ `config/services.php` → removed `openai` config
- ✅ `.env` → removed `OPENAI_API_KEY` and `OPENAI_MODEL`

## Migration Checklist

- [x] Create WebhookAiService
- [x] Replace OpenAiAnalysisService in all Livewire components
- [x] Replace OpenAiAnalysisService in all controllers
- [x] Update PromptTester to use webhook
- [x] Remove OpenAiAnalysisService file
- [x] Clean up config/services.php
- [x] Update .env with webhook URLs
- [x] Clear all caches
- [ ] Set up n8n workflow for ai-analysis webhook
- [ ] Test todo analysis via webhook
- [ ] Test company extraction via webhook
- [ ] Test goals extraction via webhook

## Troubleshooting

### Error: "AI analysis webhook URL not configured"

**Solution:** Add `N8N_AI_ANALYSIS_WEBHOOK_URL` to `.env` file

### Error: "Webhook failed: 404"

**Solution:** n8n workflow not activated or wrong URL. Check n8n dashboard.

### Error: "Invalid webhook response format"

**Solution:** n8n must return JSON with `success` field. Check workflow response node.

### Slow responses

**Solution:** 
- Check n8n execution logs for delays
- Increase timeout in WebhookAiService if needed
- Consider caching frequently used prompts

## Future Enhancements

- [ ] Add webhook authentication (HMAC signatures)
- [ ] Implement response caching for identical prompts
- [ ] Add fallback to different LLM provider via n8n
- [ ] Monitor token usage and costs via n8n
- [ ] A/B testing different prompts via n8n workflows
