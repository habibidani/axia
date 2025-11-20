# Axia - AI Focus Coach for Early-Stage Founders

Axia is an AI-powered focus coach that helps early-stage founders prioritize their to-do lists based on their business goals and KPIs. It analyzes tasks, provides impact scores, and suggests what to prioritize, delegate, or drop.

## Features

- **Smart Task Analysis**: AI-powered evaluation of tasks against your goals and KPIs
- **Focus Reports**: Visual, color-coded task rankings with actionable recommendations
- **Goal & KPI Management**: Define objectives and track key performance indicators
- **Guest Mode**: Try the app without creating an account
- **CSV Support**: Upload task lists or export results as CSV
- **Mobile-First Design**: Responsive, Airbnb-inspired UI

## Tech Stack

- **Backend**: Laravel 12, Livewire, Fortify
- **Frontend**: Tailwind CSS 4, Alpine.js (via Livewire)
- **Database**: SQLite (dev), PostgreSQL (production ready)
- **AI**: n8n Webhooks → OpenAI GPT-4 (see [WEBHOOK_AI_ARCHITECTURE.md](WEBHOOK_AI_ARCHITECTURE.md))

## Installation

1. **Clone and install dependencies**:
```bash
composer install
npm install
```

2. **Set up environment**:
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configure n8n Webhooks**:
Add webhook URLs to `.env`:
```
N8N_AGENT_WEBHOOK_URL=https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d
N8N_AI_ANALYSIS_WEBHOOK_URL=https://n8n.getaxia.de/webhook/ai-analysis
```
See [WEBHOOK_AI_ARCHITECTURE.md](WEBHOOK_AI_ARCHITECTURE.md) for details.

4. **Run migrations**:
```bash
php artisan migrate
```

5. **Build assets and start server**:
```bash
npm run build
php artisan serve
```

Or use the dev script for hot reload:
```bash
composer run dev
```

## Usage

1. **Login**: Visit `/login` and enter your email (or continue as guest)
2. **Setup**: Add company info and define your goals & KPIs
3. **Analyze**: Paste your to-do list on the home screen
4. **Review**: Get a focus report with task rankings and missing high-impact tasks
5. **Export**: Download results as CSV

## Database Schema

- **users**: User accounts (supports guests)
- **companies**: Company profiles
- **goals**: Business objectives
- **goal_kpis**: Key performance indicators
- **runs**: Analysis runs
- **todos**: Task items
- **todo_evaluations**: AI evaluations with scores and recommendations
- **missing_todos**: Suggested high-impact tasks

## AI Architecture

All AI processing is handled via n8n webhooks. No direct OpenAI API calls from Laravel.

See [WEBHOOK_AI_ARCHITECTURE.md](WEBHOOK_AI_ARCHITECTURE.md) for complete documentation.

**Key Service**: `app/Services/WebhookAiService.php`

## Routes

- `/` - Redirects to login
- `/login` - Login/guest access
- `/home` - Main dashboard
- `/company/edit` - Edit company info
- `/goals/edit` - Manage goals & KPIs
- `/results/{run}` - View focus report

## Development

Run all services concurrently:
```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server with hot reload

## Testing

```bash
composer test
```

## n8n Integration via MCP Server

Axia provides a Model Context Protocol (MCP) server that wraps the REST API, making it easy to integrate with n8n AI agents.

### MCP Server Features

The Axia MCP server exposes the following **tools**:
- `get_user` - Get current user profile and company
- `get_goals` - List all goals with KPIs
- `create_goal` - Create a new business goal
- `get_runs` - Get analysis runs with filtering
- `create_todos` - Create todos and run AI analysis
- `analyze_todos` - Get detailed AI recommendations

And **resources**:
- `axia://user` - Current user profile (JSON)
- `axia://goals` - All goals with KPIs (JSON)
- `axia://runs/recent` - 10 most recent runs (JSON)

### Setup

1. **Create API Token**:
```bash
docker compose exec php-cli php artisan tinker
>>> $token = \App\Models\User::first()->createToken('n8n-mcp-server');
>>> echo $token->plainTextToken;
```

2. **Configure Environment**:
Add the token to `.env`:
```
AXIA_API_TOKEN=1|your-generated-token-here
```

3. **Start MCP Server**:
```bash
docker compose -f docker-compose.n8n.yaml up -d mcp-axia
```

4. **Configure in n8n**:
Add MCP server URL in n8n settings:
```
http://mcp-axia:8102/sse-axia
```

5. **Use in n8n Workflows**:
The Axia tools will be available in n8n AI Agent nodes. Example:
- Ask: "What are my current business goals?"
- Agent calls: `get_goals` tool
- Result: Formatted list with KPIs

### MCP Server Architecture

```
n8n → supergateway (SSE) → Axia MCP Server → Laravel API (Sanctum)
```

- **Transport**: Server-Sent Events (SSE) via supergateway
- **Authentication**: Sanctum Bearer token (transparent to n8n)
- **Port**: 8102 (SSE endpoint: `/sse-axia`)
- **Networks**: n8n-network + axia-shared-network

### Example Usage in n8n

**Scenario**: Create todos and get AI analysis

```javascript
// In n8n AI Agent:
"Create these todos for goal X: [list]"

// MCP server calls:
1. create_todos({ goal_id: "X", todos: [...], analyze: true })
2. Returns analysis with impact scores and recommendations
```

For more details, see the [full API documentation](API_DOCS.md).

## License

MIT


