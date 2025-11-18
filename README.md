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
- **AI**: OpenAI GPT-4 API

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

3. **Configure OpenAI API**:
Add your OpenAI API key to `.env`:
```
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=gpt-4-turbo-preview
```

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

## API Configuration

The app uses OpenAI's API for task analysis. Configure in `config/services.php`:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
],
```

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

## License

MIT


