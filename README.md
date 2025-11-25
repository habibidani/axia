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

## Quick Start (Docker)

```bash
# 1. Clone repository
git clone https://github.com/habibidani/axia.git
cd axia

# 2. Environment setup
cp .env.example .env
docker compose run --rm php-cli php artisan key:generate

# 3. Start containers
docker compose up -d

# 4. Install dependencies
docker compose exec php-cli composer install

# 5. Run migrations
docker compose exec php-cli php artisan migrate --seed

# 6. Open app
open http://localhost
```

## Development Setup

For detailed development instructions including n8n setup, MCP servers, and debugging, see [DEV_SETUP.md](DEV_SETUP.md).

**Key services:**
- Laravel: http://localhost
- n8n: http://localhost:5678 (via `docker-compose.n8n.yaml`)
- PostgreSQL: localhost:5432

## Usage

1. **Login**: Visit `/login` and enter your email (or continue as guest)
2. **Setup**: Add company info and define your goals & KPIs
3. **Analyze**: Paste your to-do list on the home screen
4. **Review**: Get a focus report with task rankings and missing high-impact tasks
5. **Export**: Download results as CSV

## Backend Architecture

### Database Schema

```mermaid
erDiagram
    USERS ||--o| COMPANIES : "owns (1:1)"
    USERS ||--o{ RUNS : "creates"
    USERS ||--o{ AGENT_SESSIONS : "has"
    USERS ||--o{ WEBHOOK_PRESETS : "has"
    
    COMPANIES ||--o{ GOALS : "has"
    COMPANIES ||--o{ RUNS : "belongs_to"
    COMPANIES ||--o{ GOAL_KPIS : "standalone_kpis"
    
    GOALS ||--o{ GOAL_KPIS : "has_kpis"
    GOALS ||--o{ TODO_EVALUATIONS : "primary_goal"
    GOALS ||--o{ MISSING_TODOS : "missing_for_goal"
    
    GOAL_KPIS ||--o{ TODO_EVALUATIONS : "primary_kpi"
    GOAL_KPIS ||--o{ MISSING_TODOS : "missing_for_kpi"
    GOAL_KPIS ||--o{ RUNS : "snapshot_top_kpi"
    
    RUNS ||--o{ TODOS : "has"
    RUNS ||--o{ TODO_EVALUATIONS : "has"
    RUNS ||--o{ MISSING_TODOS : "has"
    RUNS ||--o{ AI_LOGS : "has"
    
    TODOS ||--|| TODO_EVALUATIONS : "has_evaluation"
    
    SYSTEM_PROMPTS ||--o{ AI_LOGS : "used_in"

    USERS {
        uuid id PK
        string first_name
        string last_name
        string email
        boolean is_guest
        string n8n_webhook_url
        json webhook_config
    }
    
    COMPANIES {
        uuid id PK
        uuid owner_user_id FK
        string name
        enum business_model
        integer team_size
        text customer_profile
    }
    
    GOALS {
        uuid id PK
        uuid company_id FK
        string title
        enum priority
        boolean is_active
    }
    
    GOAL_KPIS {
        uuid id PK
        uuid goal_id FK "nullable"
        uuid company_id FK "nullable"
        string name
        decimal current_value
        decimal target_value
        boolean is_top_kpi
    }
    
    RUNS {
        uuid id PK
        uuid company_id FK
        uuid user_id FK
        date period_start
        date period_end
        integer overall_score
    }
    
    TODOS {
        uuid id PK
        uuid run_id FK
        text normalized_title
        enum source "paste|csv"
        integer position
    }
    
    TODO_EVALUATIONS {
        uuid id PK
        uuid run_id FK
        uuid todo_id FK
        enum color "green|yellow|orange"
        integer score
        enum action_recommendation "keep|delegate|drop"
    }
    
    MISSING_TODOS {
        uuid id PK
        uuid run_id FK
        string title
        enum category "hiring|prioritization|delegation|culture"
        integer impact_score
    }
    
    SYSTEM_PROMPTS {
        uuid id PK
        enum type "todo_analysis|company_extraction|goals_extraction"
        text system_message
        decimal temperature
        boolean is_active
    }
    
    AI_LOGS {
        uuid id PK
        uuid run_id FK
        enum prompt_type
        json input_context
        json response
        boolean success
    }
    
    WEBHOOK_PRESETS {
        uuid id PK
        uuid user_id FK
        string name
        string webhook_url
        boolean is_active
    }
    
    AGENT_SESSIONS {
        uuid id PK
        uuid user_id FK
        string mode "chat|workflow"
        timestamp expires_at
    }
```

### Service Architecture

```mermaid
graph TB
    subgraph "Laravel Application"
        Controllers[Controllers<br/>Web & API]
        Livewire[Livewire Components<br/>Home, Results, Settings]
        Services[Services Layer]
        Models[Eloquent Models<br/>12 Models]
        
        Controllers --> Services
        Livewire --> Services
        Services --> Models
    end
    
    subgraph "AI Services"
        WebhookAI[WebhookAiService<br/>Main AI Gateway]
        UserContext[UserContextService<br/>Context Builder]
        Validator[AiResponseValidator<br/>Response Validation]
        
        WebhookAI --> UserContext
        WebhookAI --> Validator
    end
    
    subgraph "External Integration"
        N8N[n8n Workflow<br/>Webhook Handler]
        OpenAI[OpenAI GPT-4<br/>AI Analysis]
        
        N8N --> OpenAI
    end
    
    subgraph "MCP Server"
        MCP[MCP Server<br/>Node.js SSE]
        API[Laravel API<br/>Sanctum Auth]
        
        MCP --> API
    end
    
    Services --> WebhookAI
    WebhookAI -->|HTTP POST| N8N
    N8N -->|AI Response| WebhookAI
    
    N8N -.->|Uses Tools| MCP
    
    Models --> DB[(PostgreSQL<br/>axia_dev)]
    
    style WebhookAI fill:#ff6b6b
    style N8N fill:#4ecdc4
    style OpenAI fill:#95e1d3
    style MCP fill:#f38181
```

### Test Coverage

**Comprehensive Test Suite** (84 tests passing):
- ✅ Model CRUD Operations (all 12 models)
- ✅ Relationship Tests (14 relationship groups)
- ✅ Business Logic (webhook activation, KPI calculations, scoring)
- ✅ Service Integration (WebhookAiService, UserContextService)
- ✅ Cascade Deletes & Constraints

**Run Tests:**
```bash
# All tests
./run-tests.ps1

# Specific suite
./test-quick.ps1 relationships
./test-quick.ps1 business
./test-quick.ps1 services
```

**Test Database:** PostgreSQL `axia_test` (separate from dev database)

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


