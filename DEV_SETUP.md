# Axia Development Environment

## Schnellstart

```bash
# 1. Environment-Datei kopieren
cp .env.example .env.dev

# 2. APP_KEY generieren (einmalig)
docker compose -f docker-compose.dev.yaml run --rm php-cli php artisan key:generate

# 3. Container starten
docker compose -f docker-compose.dev.yaml up -d

# 4. Dependencies installieren
docker compose -f docker-compose.dev.yaml exec php-cli composer install

# 5. Datenbank migrieren
docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate --seed

# 6. Dateirechte setzen
docker compose -f docker-compose.dev.yaml exec php-fpm chown -R www-data:www-data storage bootstrap/cache

# 7. Application aufrufen
open http://localhost:8080
```

## Verfügbare Services

| Service        | URL                   | Beschreibung           |
| -------------- | --------------------- | ---------------------- |
| **Web**        | http://localhost:8080 | Laravel Application (nginx) |
| **Vite**       | http://localhost:5173 | Hot Module Replacement |
| **MailHog**    | http://localhost:8025 | Email Testing UI       |
| **PostgreSQL** | localhost:5432        | Datenbank (axia_dev)   |
| **Redis**      | localhost:6379        | Cache & Queue          |

## n8n Stack Services (separates docker-compose)

| Service        | URL                   | Beschreibung           |
| -------------- | --------------------- | ---------------------- |
| **n8n**        | http://localhost:5678 | Workflow Automation    |
| **mcp-axia**   | http://localhost:8102 | Axia MCP Server (SSE)  |
| **mcp-chart**  | http://localhost:8103 | Chart Generation (SSE) |
| **mcp-duckduckgo** | http://localhost:8100 | Search MCP (SSE)    |
| **mcp-notion** | http://localhost:8101 | Notion MCP (SSE)       |

## Development Workflow

### Code-Änderungen

Alle Änderungen in PHP/Blade-Dateien werden **sofort** reflektiert (Volume-Mount).

Frontend-Assets (JS/CSS) mit Vite:

```bash
# Development Mode (lokal)
npm run dev

# Build für Production
npm run build
```

### Artisan Commands

```bash
# Migration erstellen
docker compose exec php-cli php artisan make:migration create_xyz_table

# Migration ausführen
docker compose exec php-cli php artisan migrate

# Tinker (REPL)
docker compose exec php-cli php artisan tinker

# Cache leeren
docker compose exec php-cli php artisan cache:clear
```

### Datenbank

```bash
# PostgreSQL CLI
docker compose exec postgres psql -U postgres -d axia_db

# Datenbank resetten
docker compose exec php-cli php artisan migrate:fresh --seed
```

### Debugging mit Xdebug

#### VS Code Launch Configuration

Erstelle `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www": "${workspaceFolder}"
            }
        }
    ]
}
```

#### VS Code Extension

Installiere: **PHP Debug** von Xdebug

#### Debugging starten

1. Setze Breakpoint in VS Code
2. Drücke F5 (oder "Run > Start Debugging")
3. Lade Seite im Browser: http://localhost:8080
4. VS Code pausiert am Breakpoint

### Logs anzeigen

```bash
# Alle Services
docker compose logs -f

# Nur PHP-FPM
docker compose logs -f php-fpm

# Laravel Log
docker compose exec php-cli tail -f storage/logs/laravel.log

# n8n Services
docker compose -f docker-compose.n8n.yaml logs -f
```

### n8n Workflows

n8n ist das Herzstück der AI-Integration. Alle AI-Anfragen laufen über n8n-Webhooks:

```bash
# n8n Stack starten
docker compose -f docker-compose.n8n.yaml up -d

# n8n UI öffnen
open http://localhost:5678

# MCP Server Logs
docker compose -f docker-compose.n8n.yaml logs -f mcp-axia
docker compose -f docker-compose.n8n.yaml logs -f mcp-chart
```

**Wichtige Webhooks:**
- AI Analysis: `https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d`
- Chart Generation: `https://n8n.getaxia.de/webhook/c3352634-be98-4448-903a-d04ed64ea90b`

### Frontend Development

```bash
# Dependencies installieren
npm install

# Development Server (mit HMR)
npm run dev

# Build für Production
npm run build
```

## Aliases (Optional)

Füge in `~/.bashrc` oder `~/.zshrc` hinzu:

```bash
alias dc='docker compose'
alias dcn='docker compose -f docker-compose.n8n.yaml'
alias dart='docker compose exec php-cli php artisan'
alias dcomposer='docker compose exec php-cli composer'
```

Dann:

```bash
dc up -d
dcn up -d
dart migrate
dcomposer require vendor/package
```

## Troubleshooting

### Port bereits belegt

```bash
# Port 80 belegt
# Ändere in docker-compose.yaml: ports: "8080:80"

# PostgreSQL Port 5432 belegt
# Ändere in docker-compose.yaml: ports: "5433:5432"
# Dann auch .env: DB_PORT=5433
```

### Permission Errors

```bash
# Storage permissions
docker compose exec php-fpm chown -R www-data:www-data storage bootstrap/cache
docker compose exec php-cli chmod -R 775 storage bootstrap/cache

# Composer cache
docker compose exec php-cli composer clear-cache
```

### Container neustarten

```bash
# Alle Container
docker compose restart

# Nur php-fpm
docker compose restart php-fpm

# n8n Stack neustarten
docker compose -f docker-compose.n8n.yaml restart

# Rebuild bei Dockerfile-Änderungen
docker compose build --no-cache
docker compose up -d
```

### Volumes löschen

```bash
# Alle Volumes löschen (ACHTUNG: Daten gehen verloren!)
docker compose down -v

# n8n Volumes löschen
docker compose -f docker-compose.n8n.yaml down -v

# Dann neu starten
docker compose up -d
docker compose exec php-cli php artisan migrate:fresh --seed
```

## Deployment vs Development

| Feature        | Development      | Production         |
| -------------- | ---------------- | ------------------ |
| **Debug**      | APP_DEBUG=true   | APP_DEBUG=false    |
| **Errors**     | Angezeigt        | Geloggt            |
| **OPcache**    | Deaktiviert      | Aktiviert          |
| **Code Mount** | Volume-Mount     | Baked in Image     |
| **Assets**     | npm run dev      | Pre-built          |
| **Domain**     | localhost        | www.getaxia.de     |
| **n8n**        | localhost:5678   | n8n.getaxia.de     |
| **HTTPS**      | Nein             | Let's Encrypt      |

## Best Practices

1. **Niemals** `.env.dev` committen (schon in `.gitignore`)
2. **Immer** `php artisan config:clear` nach `.env` Änderungen
3. **Regelmäßig** `composer update` für Dependencies
4. **Tests schreiben** vor Push: `php artisan test`
5. **Migrations** testen mit `migrate:fresh` vor Production-Deploy
