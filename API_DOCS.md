# Axia API Documentation

## Overview

The Axia API allows you to integrate Axia with n8n and other automation tools. Use this API to:

- Read user goals, KPIs, and analysis runs
- Create new todos programmatically
- Trigger webhooks when events occur
- Sync data between Axia and external tools (Notion, Slack, etc.)

**Base URL (Docker internal):** `http://axia-php-fpm-1:9000/api`  
**Base URL (External):** `https://www.getaxia.de/api`

---

## Authentication

All API requests require a Bearer token. Generate tokens in **Settings > API Tokens**.

### Example Headers
```http
Authorization: Bearer {your_api_token}
Content-Type: application/json
Accept: application/json
```

### n8n HTTP Request Node Configuration
```json
{
  "authentication": "genericCredentialType",
  "genericAuthType": "httpHeaderAuth",
  "httpHeaderAuth": {
    "name": "Authorization",
    "value": "Bearer YOUR_TOKEN_HERE"
  }
}
```

---

## Endpoints

### User & Company

#### `GET /api/user`
Get authenticated user information.

**Response:**
```json
{
  "data": {
    "id": "019a9c6f-5afb-7184-9848-75c58cb678b9",
    "first_name": "Admin",
    "last_name": "User",
    "email": "info@getaxia.de",
    "is_guest": false,
    "created_at": "2025-11-19T14:06:10.000000Z"
  }
}
```

#### `GET /api/user/company`
Get user's company with goals and recent runs.

**Response:**
```json
{
  "data": {
    "id": "019a9c6f-5b24-72ec-a3d5-d6e8f9abc123",
    "name": "Axia GmbH",
    "business_model": "b2b_saas",
    "team_cofounders": 2,
    "team_employees": 5,
    "goals": [
      {
        "id": "019a9c6f-5b3e-7f2a-9d1c-e4f5a6b7c8d9",
        "title": "Increase MRR by 50%",
        "description": "Grow monthly recurring revenue from €10k to €15k",
        "priority": "high",
        "time_frame": "6 months",
        "is_active": true,
        "kpis": [
          {
            "id": "019a9c6f-5b4f-7a3b-8c2d-9e0f1a2b3c4d",
            "name": "MRR",
            "unit": "EUR",
            "current_value": 10000,
            "target_value": 15000,
            "tracking_frequency": "monthly"
          }
        ]
      }
    ],
    "runs": []
  }
}
```

---

### Goals & KPIs

#### `GET /api/goals`
List all goals for the user's company.

**Response:**
```json
{
  "data": [
    {
      "id": "...",
      "title": "Launch MVP",
      "description": "Complete and launch minimum viable product for beta testing",
      "priority": "high",
      "time_frame": "3 months",
      "is_active": true,
      "kpis": [...]
    }
  ]
}
```

#### `POST /api/goals`
Create a new goal.

**Request Body:**
```json
{
  "title": "Acquire 100 Beta Users",
  "description": "Get first 100 active beta testers",
  "priority": "medium",
  "time_frame": "4 months",
  "is_active": true
}
```

**Response:** `201 Created`

#### `GET /api/goals/{goal}`
Get a specific goal with KPIs.

#### `PUT /api/goals/{goal}`
Update a goal.

#### `DELETE /api/goals/{goal}`
Delete a goal.

#### `POST /api/goals/{goal}/kpis`
Add a KPI to a goal.

**Request Body:**
```json
{
  "name": "User Signups",
  "unit": "users",
  "current_value": 20,
  "target_value": 100,
  "tracking_frequency": "weekly"
}
```

---

### Todos & Runs

#### `GET /api/runs`
List all analysis runs (paginated, 20 per page).

**Response:**
```json
{
  "data": [
    {
      "id": "...",
      "company_id": "...",
      "user_id": "...",
      "overall_score": 75,
      "summary_text": "Focus on green items...",
      "created_at": "2025-11-19T10:30:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

#### `GET /api/runs/{run}`
Get detailed run with todos and evaluations.

**Response:**
```json
{
  "data": {
    "id": "...",
    "overall_score": 75,
    "summary_text": "Focus on green items first...",
    "todos": [
      {
        "id": "...",
        "raw_input": "Hire senior engineer",
        "source": "paste",
        "evaluation": {
          "color": "green",
          "score": 95,
          "reasoning": "Critical for product development...",
          "priority_recommendation": "do_now"
        }
      }
    ],
    "missing_todos": [
      {
        "title": "Set up customer onboarding flow",
        "impact_score": 90
      }
    ]
  }
}
```

#### `POST /api/todos`
Create todos and trigger AI analysis (creates a new run).

**Request Body:**
```json
{
  "todos": [
    "Review Q1 metrics with team",
    "Hire senior engineer",
    "Update investor deck"
  ]
}
```

**Response:** `201 Created` with full run data

#### `POST /api/todos/batch`
Add todos to existing run (no analysis).

**Request Body:**
```json
{
  "run_id": "019a9c6f-...",
  "todos": [
    {"title": "New task from Notion", "source": "notion"},
    {"title": "Follow up with client"}
  ]
}
```

---

### Webhooks

#### `POST /api/webhooks/run-completed`
Trigger n8n webhook when run is completed.

**Request Body:**
```json
{
  "run_id": "019a9c6f-..."
}
```

**n8n will receive:**
```json
{
  "user_id": "...",
  "run_id": "...",
  "overall_score": 75,
  "summary_text": "Focus on green items...",
  "top_priority_todos": [...],
  "missing_todos": [...],
  "timestamp": "2025-11-19T14:30:00+00:00"
}
```

#### `POST /api/webhooks/goal-achieved`
Trigger n8n webhook when goal is achieved.

**Request Body:**
```json
{
  "goal_id": "...",
  "kpi_id": "..." 
}
```

---

## n8n Integration Examples

### Example 1: Daily Goal Digest

**Workflow:**
1. **Schedule Trigger** (every morning at 9 AM)
2. **HTTP Request** → `GET https://www.getaxia.de/api/goals`
   - Auth: Bearer Token
3. **Function Node** → Filter active goals
4. **Send to Slack** → Post daily focus

```javascript
// Function Node
const activeGoals = $input.all()[0].json.data.filter(g => g.is_active);
return activeGoals.map(goal => ({
  json: {
    text: `🎯 ${goal.title} (${goal.priority})`,
    kpis: goal.kpis.map(k => `${k.name}: ${k.current_value}/${k.target_value} ${k.unit}`)
  }
}));
```

### Example 2: Notion → Axia Sync

**Workflow:**
1. **Notion Trigger** (Database item created)
2. **Function Node** → Extract todo text
3. **HTTP Request** → `POST https://www.getaxia.de/api/todos`
   ```json
   {
     "todos": ["{{ $json.properties.Title.title[0].text.content }}"]
   }
   ```
4. **Wait for Analysis** (30 seconds)
5. **HTTP Request** → `GET /api/runs/{run_id}/evaluations`
6. **Update Notion** → Add priority tag based on evaluation color

### Example 3: Auto-create Todos from Slack

**Workflow:**
1. **Slack Trigger** (slash command `/addtodo`)
2. **HTTP Request** → `POST https://www.getaxia.de/api/todos/batch`
   ```json
   {
     "run_id": "{{ $env.CURRENT_RUN_ID }}",
     "todos": [
       {"title": "{{ $json.text }}", "source": "slack"}
     ]
   }
   ```
3. **Reply to Slack** → Confirmation message

---

## Docker Internal Communication

When n8n and Axia run in Docker on the same host:

**n8n can reach Axia at:**
- `http://axia-php-fpm-1:9000/api` (php-fpm container name)
- Or use the shared network: `http://php-fpm:9000/api`

**No HTTPS required** for internal Docker communication (Synology proxy handles external HTTPS).

---

## Rate Limits

- **60 requests per minute** per API token
- Returns `429 Too Many Requests` if exceeded

---

## Error Responses

```json
{
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

**Status Codes:**
- `200` OK
- `201` Created
- `401` Unauthorized (invalid/missing token)
- `403` Forbidden (resource doesn't belong to user)
- `404` Not Found
- `422` Validation Error
- `429` Rate Limit Exceeded
- `500` Server Error

---

## Security Best Practices

1. **Token Rotation**: Regenerate tokens every 90 days
2. **Least Privilege**: Create separate tokens for different workflows
3. **Monitor Usage**: Check "Last Used" in Settings > API Tokens
4. **Revoke Immediately**: Delete compromised tokens ASAP
5. **HTTPS Only**: Always use HTTPS for external requests

---

## Support

Questions? Check the [GitHub Issues](https://github.com/habibidani/axia/issues) or email info@getaxia.de
