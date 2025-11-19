# Axia MCP Server - Complete Guide

The Axia MCP (Model Context Protocol) Server provides a standardized interface for integrating Axia with n8n AI agents and other MCP-compatible tools.

## Architecture Overview

```
┌─────────┐     SSE      ┌──────────────┐     HTTP      ┌─────────────┐
│   n8n   │ ──────────→  │ supergateway │ ──────────→   │ Axia MCP    │
│         │              │              │               │ Server      │
└─────────┘              └──────────────┘               └─────────────┘
                                                                │
                                                                │ Sanctum
                                                                │ Bearer Token
                                                                ↓
                                                         ┌─────────────┐
                                                         │ Laravel API │
                                                         │ (Axia)      │
                                                         └─────────────┘
```

## Available Tools

### 1. get_user
Get the current authenticated user profile and company information.

**Parameters:**
- `include_company` (boolean, optional): Include company details. Default: `true`

**Example:**
```json
{
  "include_company": true
}
```

**Response:**
```
User Profile:
- Name: John Doe
- Email: john@startup.com
- Company: Startup Inc.
- Role: Founder
```

---

### 2. get_goals
List all business goals with optional KPI data.

**Parameters:**
- `include_kpis` (boolean, optional): Include KPI metrics. Default: `true`

**Example:**
```json
{
  "include_kpis": true
}
```

**Response:**
```
Found 3 goals:

1. Increase MRR to $10k
   ID: 123e4567-e89b-12d3-a456-426614174000
   Priority: high
   Status: Active
   KPIs:
     - Monthly Revenue: $7,500/$10,000 USD
     - Customer Count: 45/60 customers

2. Reduce Churn Rate
   ID: 223e4567-e89b-12d3-a456-426614174001
   Priority: medium
   Status: Active
   KPIs:
     - Churn Rate: 8%/5% percentage
```

---

### 3. create_goal
Create a new business goal with SMART criteria.

**Parameters:**
- `title` (string, required): Goal title (SMART format recommended)
- `description` (string, optional): Detailed description
- `priority` (enum, optional): `low`, `medium`, `high`. Default: `medium`
- `time_frame` (string, optional): e.g., "6 months", "Q2 2025"

**Example:**
```json
{
  "title": "Launch MVP by Q2 2025",
  "description": "Build and ship minimal viable product with core features",
  "priority": "high",
  "time_frame": "3 months"
}
```

**Response:**
```
Goal created successfully!
ID: 323e4567-e89b-12d3-a456-426614174002
Title: Launch MVP by Q2 2025
```

---

### 4. get_runs
Retrieve task analysis runs with optional filtering.

**Parameters:**
- `limit` (number, optional): Max number of runs to return. Default: `10`
- `goal_id` (string, optional): Filter by specific goal

**Example:**
```json
{
  "limit": 5,
  "goal_id": "123e4567-e89b-12d3-a456-426614174000"
}
```

**Response:**
```
Found 5 runs:

1. Run #run-001
   Goal: Increase MRR to $10k
   Status: completed
   Todos: 12
   Created: 2025-01-15

2. Run #run-002
   Goal: Increase MRR to $10k
   Status: completed
   Todos: 8
   Created: 2025-01-18
```

---

### 5. create_todos
Create todos for a goal and optionally run AI analysis.

**Parameters:**
- `goal_id` (string, required): Goal UUID
- `todos` (array of strings, required): List of todo descriptions
- `analyze` (boolean, optional): Run AI analysis. Default: `true`

**Example:**
```json
{
  "goal_id": "123e4567-e89b-12d3-a456-426614174000",
  "todos": [
    "Set up payment processing with Stripe",
    "Design pricing page mockups",
    "Write email sequence for trial users",
    "Update landing page with new features"
  ],
  "analyze": true
}
```

**Response:**
```
Todos created successfully!
Run ID: run-003
Status: completed

AI Analysis:
Based on your goal to increase MRR to $10k, here's the priority order:

HIGH PRIORITY (Do First):
1. Set up payment processing with Stripe
   - Impact Score: 9/10
   - Directly enables revenue generation
   
2. Write email sequence for trial users
   - Impact Score: 8/10
   - Improves conversion funnel

MEDIUM PRIORITY (Do Next):
3. Design pricing page mockups
   - Impact Score: 6/10
   - Supports conversion but can iterate

LOW PRIORITY (Delegate/Drop):
4. Update landing page with new features
   - Impact Score: 4/10
   - Nice to have but not critical for MRR goal

MISSING HIGH-IMPACT TASKS:
- Set up analytics to track conversion rates
- Create customer onboarding checklist
```

---

### 6. analyze_todos
Get detailed AI analysis for a specific run.

**Parameters:**
- `run_id` (string, required): Run identifier

**Example:**
```json
{
  "run_id": "run-003"
}
```

**Response:**
```
Analysis for Run #run-003:

Goal: Increase MRR to $10k
Status: completed
Total Todos: 4

Summary:
Your tasks are well-aligned with revenue growth. Focus on Stripe integration
first as it unblocks monetization. The email sequence is your next high-leverage
activity. Consider outsourcing the design work.

Todos:
1. Set up payment processing with Stripe
   Impact Score: 9/10
   Recommendation: do
   Reason: Critical blocker for revenue - do this first

2. Write email sequence for trial users
   Impact Score: 8/10
   Recommendation: do
   Reason: High conversion impact with good ROI

3. Design pricing page mockups
   Impact Score: 6/10
   Recommendation: delegate
   Reason: Important but can be delegated to designer

4. Update landing page with new features
   Impact Score: 4/10
   Recommendation: drop
   Reason: Low impact relative to other priorities
```

---

## Available Resources

### axia://user
Current user profile with company information (JSON format).

### axia://goals
Complete list of goals with KPIs (JSON format).

### axia://runs/recent
The 10 most recent analysis runs (JSON format).

---

## Setup Instructions

### 1. Generate API Token

Connect to your Laravel container and create a Sanctum token:

```bash
docker compose exec php-cli php artisan tinker
```

In Tinker:
```php
$user = \App\Models\User::first();
$token = $user->createToken('n8n-mcp-server');
echo $token->plainTextToken;
// Copy the output: 1|AbCd... (this is your AXIA_API_TOKEN)
exit
```

### 2. Configure Environment

Add the token to your `.env` file:

```bash
# Axia MCP Server Configuration
AXIA_API_TOKEN=1|your-generated-token-here
```

### 3. Start the MCP Server

```bash
docker compose -f docker-compose.n8n.yaml up -d mcp-axia
```

Verify it's running:
```bash
docker compose -f docker-compose.n8n.yaml ps mcp-axia
docker compose -f docker-compose.n8n.yaml logs mcp-axia
```

### 4. Test SSE Endpoint

```bash
curl http://localhost:8102/sse-axia
```

Expected: SSE event stream starts.

---

## Integration with n8n

### Configure MCP Server in n8n

1. **Open n8n Settings** (http://localhost:5678)
2. **Navigate to**: Settings → AI → MCP Servers
3. **Add New Server**:
   - Name: `Axia`
   - URL: `http://mcp-axia:8102/sse-axia`
   - Transport: `SSE`
4. **Save** and test connection

### Example Workflow: Daily Goal Review

1. **Schedule Trigger** (daily at 9am)
2. **AI Agent Node**:
   - System Prompt: "You are a business coach helping founders prioritize"
   - User Message: "Review my goals and suggest today's top 3 priorities"
   - Tools: Enable Axia MCP tools (`get_goals`, `get_runs`, etc.)
3. **Send Email** with AI-generated priorities

### Example Workflow: Todo Analysis

1. **Webhook Trigger** (receives todo list from Slack/Email)
2. **AI Agent Node**:
   - Extract goal_id from message
   - Call `create_todos` tool with analysis enabled
3. **Format Response** (Markdown/Slack blocks)
4. **Post to Slack** with color-coded priorities

---

## Advanced Usage

### Custom Analysis Prompts

The MCP server can be extended with custom prompts. Edit `mcp-server/index.js`:

```javascript
// Add new tool
{
  name: 'suggest_tasks',
  description: 'AI suggests tasks for a goal based on industry best practices',
  inputSchema: {
    type: 'object',
    properties: {
      goal_id: { type: 'string' },
      industry: { type: 'string' },
    },
    required: ['goal_id'],
  },
}
```

### Monitoring and Debugging

**View MCP Server Logs:**
```bash
docker compose -f docker-compose.n8n.yaml logs -f mcp-axia
```

**Check API Connectivity:**
```bash
docker compose -f docker-compose.n8n.yaml exec mcp-axia sh
# Inside container:
curl -H "Authorization: Bearer $AXIA_API_TOKEN" http://axia-php-fpm-1/api/user
```

**Test Individual Tools:**
Use the MCP client inspector (if available) or call tools directly from n8n.

---

## Troubleshooting

### MCP Server Won't Start

**Check logs:**
```bash
docker compose -f docker-compose.n8n.yaml logs mcp-axia
```

**Common issues:**
- `AXIA_API_TOKEN` not set → Add to `.env`
- Can't reach Laravel API → Check `axia-shared-network` exists
- Node modules missing → Rebuild container

### Tools Return Errors

**"Unauthorized" (401)**:
- Token expired or invalid
- Regenerate token in Tinker
- Update `.env` with new token
- Restart MCP server: `docker compose -f docker-compose.n8n.yaml restart mcp-axia`

**"Not Found" (404)**:
- API route doesn't exist
- Check `routes/api.php` in Laravel
- Ensure Sanctum middleware is applied

**"Server Error" (500)**:
- Check Laravel logs: `docker compose logs -f php-fpm`
- Check database connection
- Verify goal/run IDs exist

### n8n Can't Connect to MCP Server

**Network issues:**
```bash
# Check if both containers are on same network
docker network inspect n8n-network
```

**Port issues:**
```bash
# Test from n8n container
docker compose -f docker-compose.n8n.yaml exec n8n curl http://mcp-axia:8102/sse-axia
```

---

## Security Considerations

1. **Token Management**:
   - Never commit `.env` files with tokens
   - Rotate tokens periodically
   - Create dedicated tokens per integration (one for MCP, one for n8n HTTP, etc.)

2. **Network Isolation**:
   - MCP server only exposed on internal Docker networks
   - No direct public internet access
   - Use reverse proxy with auth if exposing externally

3. **Rate Limiting**:
   - Consider adding rate limits to API routes
   - Use Laravel's built-in throttle middleware

---

## Performance Tips

1. **Caching**:
   - Goals/KPIs change infrequently → cache for 5 minutes
   - User profile → cache per session

2. **Batching**:
   - Use `create_todos` with analyze=false for bulk operations
   - Run analysis separately when ready

3. **Pagination**:
   - Use `limit` parameter for runs
   - Default is 10, increase only if needed

---

## Development

### Local Setup

```bash
# Install dependencies
cd mcp-server
npm install

# Run in dev mode (without Docker)
AXIA_API_URL=http://localhost AXIA_API_TOKEN=your-token node index.js
```

### Testing Tools

Use MCP Inspector or write a simple test script:

```javascript
// test-mcp.js
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
// ... (import your MCP server)

// Test get_goals tool
const result = await server.handleRequest({
  method: 'tools/call',
  params: {
    name: 'get_goals',
    arguments: { include_kpis: true }
  }
});

console.log(result);
```

---

## API Reference

For complete API documentation, see [API_DOCS.md](API_DOCS.md).

For Laravel setup, see [README.md](README.md).

For development environment, see [DEV_SETUP.md](DEV_SETUP.md).

---

## Support

- **Issues**: Create a GitHub issue
- **Documentation**: This file + API_DOCS.md
- **Logs**: `docker compose -f docker-compose.n8n.yaml logs mcp-axia`
