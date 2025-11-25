# Axia - AI Focus Coach for Early-Stage Founders

Axia is an AI-powered focus coach that helps early-stage founders prioritize their to-do lists based on their business goals and KPIs. It analyzes tasks, provides impact scores, and suggests what to prioritize, delegate, or drop.

## ⚡ Recent Updates

### System Prompt Security Fix (2025-11-25)

🔒 **Critical Security Enhancement**: Implemented multi-layer protection to prevent unauthorized deletion or modification of system prompts. Guest users can no longer delete essential AI prompts. See [SYSTEM_PROMPT_SECURITY.md](SYSTEM_PROMPT_SECURITY.md) for details.

**Key Changes:**

-   Added `is_system_default` flag to system prompts
-   Enhanced AdminPrompts component with deletion/edit protection
-   Created `system:restore-prompts` artisan command for recovery
-   Strengthened authentication checks for admin routes

## Features

-   **Smart Task Analysis**: AI-powered evaluation of tasks against your goals and KPIs
-   **Focus Reports**: Visual, color-coded task rankings with actionable recommendations
-   **Goal & KPI Management**: Define objectives and track key performance indicators
-   **Guest Mode**: Try the app without creating an account
-   **CSV Support**: Upload task lists or export results as CSV
-   **Mobile-First Design**: Responsive, Airbnb-inspired UI

## Tech Stack

-   **Backend**: Laravel 12, Livewire, Fortify
-   **Frontend**: Tailwind CSS 4, Alpine.js (via Livewire)
-   **Database**: SQLite (dev), PostgreSQL (production ready)
-   **AI**: n8n Webhooks → OpenAI GPT-4 (see [WEBHOOK_AI_ARCHITECTURE.md](WEBHOOK_AI_ARCHITECTURE.md))

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

-   Laravel: http://localhost
-   n8n: http://localhost:5678 (via `docker-compose.n8n.yaml`)
-   PostgreSQL: localhost:5432

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
    %% =============================================
    %% CORE USER & COMPANY
    %% =============================================

    USERS ||--|| COMPANIES : "owns (1:1)"
    USERS ||--o{ RUNS : "creates"
    USERS ||--o{ AGENT_SESSIONS : "sessions"
    USERS ||--o{ WEBHOOK_PRESETS : "webhooks"
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "tokens"

    USERS {
        uuid id PK
        varchar first_name
        varchar last_name
        varchar email UK "unique"
        timestamp email_verified_at
        varchar password
        boolean is_guest "default:false"
        varchar remember_token
        text two_factor_secret
        text two_factor_recovery_codes
        timestamp two_factor_confirmed_at
        varchar n8n_webhook_url
        json webhook_config
        timestamp created_at
        timestamp updated_at
    }

    COMPANIES {
        uuid id PK
        uuid owner_user_id FK "CASCADE"
        varchar name "NOT NULL"
        varchar business_model "b2b_saas|b2c|marketplace|agency|other"
        integer team_cofounders
        integer team_employees
        varchar user_position
        text customer_profile
        text market_insights
        varchar website
        text original_smart_text
        boolean extracted_from_text
        text additional_information
        timestamp created_at
        timestamp updated_at
    }

    %% =============================================
    %% GOALS & KPIs
    %% =============================================

    COMPANIES ||--o{ GOALS : "has goals"
    COMPANIES ||--o{ GOAL_KPIS : "standalone KPIs"
    GOALS ||--o{ GOAL_KPIS : "has KPIs"

    GOALS {
        uuid id PK
        uuid company_id FK "CASCADE"
        varchar title "NOT NULL"
        text description
        varchar priority "high|medium|low"
        varchar time_frame
        boolean is_active "default:true"
        text original_smart_text
        boolean extracted_from_text
        text additional_information
        timestamp created_at
        timestamp updated_at
    }

    GOAL_KPIS {
        uuid id PK
        uuid goal_id FK "CASCADE,nullable"
        uuid company_id FK "nullable"
        varchar name "NOT NULL"
        decimal current_value "12,2"
        decimal target_value "12,2"
        varchar unit
        varchar time_frame
        boolean is_top_kpi
        text original_smart_text
        boolean extracted_from_text
        text additional_information
        timestamp created_at
        timestamp updated_at
    }

    %% =============================================
    %% RUNS & ANALYSIS
    %% =============================================

    COMPANIES ||--o{ RUNS : "analysis runs"
    USERS ||--o{ RUNS : "created by"
    GOAL_KPIS ||--o{ RUNS : "snapshot KPI"
    RUNS ||--o{ TODOS : "has todos"
    RUNS ||--o{ TODO_EVALUATIONS : "evaluations"
    RUNS ||--o{ MISSING_TODOS : "suggestions"
    RUNS ||--o{ AI_LOGS : "logs"

    RUNS {
        uuid id PK
        uuid company_id FK "nullable"
        uuid user_id FK "NOT NULL"
        date period_start "NOT NULL"
        date period_end "NOT NULL"
        uuid snapshot_top_kpi_id FK "nullable"
        integer overall_score
        text summary_text
        timestamp created_at
        timestamp updated_at
    }

    TODOS {
        uuid id PK
        uuid run_id FK "CASCADE"
        text raw_input "NOT NULL"
        text normalized_title "NOT NULL"
        varchar owner
        date due_date
        varchar source "paste|csv"
        integer position "NOT NULL"
        timestamp created_at
        timestamp updated_at
    }

    %% =============================================
    %% TODO EVALUATIONS
    %% =============================================

    TODOS ||--|| TODO_EVALUATIONS : "evaluation"
    GOALS ||--o{ TODO_EVALUATIONS : "primary goal"
    GOAL_KPIS ||--o{ TODO_EVALUATIONS : "primary KPI"

    TODO_EVALUATIONS {
        uuid id PK
        uuid run_id FK "NOT NULL"
        uuid todo_id FK "UNIQUE,NOT NULL"
        varchar color "green|yellow|orange"
        integer score "NOT NULL"
        text reasoning
        varchar priority_recommendation "high|low|none"
        varchar action_recommendation "keep|delegate|drop"
        varchar delegation_target_role
        uuid primary_goal_id FK "nullable"
        uuid primary_kpi_id FK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    %% =============================================
    %% MISSING TODOS & AI
    %% =============================================

    GOALS ||--o{ MISSING_TODOS : "goal suggestions"
    GOAL_KPIS ||--o{ MISSING_TODOS : "KPI suggestions"

    MISSING_TODOS {
        uuid id PK
        uuid run_id FK "NOT NULL"
        uuid goal_id FK "nullable"
        uuid kpi_id FK "nullable"
        varchar title "NOT NULL"
        text description
        varchar category "hiring|prioritization|delegation|culture|other"
        integer impact_score
        varchar suggested_owner_role
        timestamp created_at
        timestamp updated_at
    }

    SYSTEM_PROMPTS ||--o{ AI_LOGS : "used in"

    SYSTEM_PROMPTS {
        uuid id PK
        varchar type "todo_analysis|company_extraction|goals_extraction"
        text system_message "NOT NULL"
        text user_prompt_template "NOT NULL"
        decimal temperature "2,1"
        boolean is_active "ONE per type"
        varchar version
        timestamp created_at
        timestamp updated_at
    }

    AI_LOGS {
        uuid id PK
        uuid run_id FK "nullable"
        varchar prompt_type
        uuid system_prompt_id FK "nullable"
        json input_context "NOT NULL"
        json response "NOT NULL"
        integer tokens_used
        integer duration_ms
        boolean success
        text error_message
        timestamp created_at
        timestamp updated_at
    }

    %% =============================================
    %% WEBHOOKS & SESSIONS
    %% =============================================

    WEBHOOK_PRESETS {
        uuid id PK
        uuid user_id FK "CASCADE"
        varchar name "NOT NULL"
        varchar webhook_url "NOT NULL"
        text description
        boolean is_active "ONE active per user"
        boolean is_default
        timestamp created_at
        timestamp updated_at
    }

    AGENT_SESSIONS {
        uuid id PK
        uuid session_id UK "UNIQUE"
        uuid user_id FK "CASCADE"
        varchar mode "chat|workflow|analysis"
        varchar workflow_key
        json meta
        timestamp expires_at "NOT NULL"
        timestamp created_at
        timestamp updated_at
    }

    PERSONAL_ACCESS_TOKENS {
        integer id PK
        varchar tokenable_type "NOT NULL"
        uuid tokenable_id "NOT NULL"
        varchar name "NOT NULL"
        varchar token UK "64,UNIQUE"
        text abilities
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }
```

**Key Relationships:**

-   👤 **User → Company**: 1:1 ownership
-   🎯 **Company → Goals**: One company has many goals
-   📊 **Goal → KPIs**: Goals track multiple KPIs (or standalone company KPIs)
-   🔄 **Run**: Analysis session linking user, company, todos, and AI evaluations
-   ✅ **Todo → Evaluation**: Each todo gets one AI evaluation (1:1)
-   💡 **Missing Todos**: AI-suggested tasks per run based on goals/KPIs
-   🤖 **System Prompts**: Versioned AI prompts (one active per type)
-   📝 **AI Logs**: Complete audit trail of all AI operations
-   🔗 **Webhook Presets**: User-specific n8n webhook configs (one active)
-   🎭 **Agent Sessions**: Temporary session storage for n8n agents### Service Architecture

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

-   ✅ Model CRUD Operations (all 12 models)
-   ✅ Relationship Tests (14 relationship groups)
-   ✅ Business Logic (webhook activation, KPI calculations, scoring)
-   ✅ Service Integration (WebhookAiService, UserContextService)
-   ✅ Cascade Deletes & Constraints

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

-   `/` - Redirects to login
-   `/login` - Login/guest access
-   `/home` - Main dashboard
-   `/company/edit` - Edit company info
-   `/goals/edit` - Manage goals & KPIs
-   `/results/{run}` - View focus report

## Development

Run all services concurrently:

```bash
composer run dev
```

This starts:

-   Laravel development server
-   Queue worker
-   Log viewer (Pail)
-   Vite dev server with hot reload

## Testing

```bash
composer test
```

## n8n Integration via MCP Server

Axia provides a Model Context Protocol (MCP) server that wraps the REST API, making it easy to integrate with n8n AI agents.

### MCP Server Features

The Axia MCP server exposes the following **tools**:

-   `get_user` - Get current user profile and company
-   `get_goals` - List all goals with KPIs
-   `create_goal` - Create a new business goal
-   `get_runs` - Get analysis runs with filtering
-   `create_todos` - Create todos and run AI analysis
-   `analyze_todos` - Get detailed AI recommendations

And **resources**:

-   `axia://user` - Current user profile (JSON)
-   `axia://goals` - All goals with KPIs (JSON)
-   `axia://runs/recent` - 10 most recent runs (JSON)

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

-   Ask: "What are my current business goals?"
-   Agent calls: `get_goals` tool
-   Result: Formatted list with KPIs

### MCP Server Architecture

```
n8n → supergateway (SSE) → Axia MCP Server → Laravel API (Sanctum)
```

-   **Transport**: Server-Sent Events (SSE) via supergateway
-   **Authentication**: Sanctum Bearer token (transparent to n8n)
-   **Port**: 8102 (SSE endpoint: `/sse-axia`)
-   **Networks**: n8n-network + axia-shared-network

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
