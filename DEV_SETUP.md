# Axia Development Environment

## Schnellstart

```bash
# 1. Environment-Datei kopieren
cp .env.example .env

# 2. APP_KEY generieren (einmalig)
docker compose -f docker-compose.dev.yaml run --rm php-cli php artisan key:generate

# 3. Container starten
docker compose -f docker-compose.dev.yaml up -d

# 4. Dependencies installieren
docker compose -f docker-compose.dev.yaml exec php-cli composer install
# Hinweis: npm install wird automatisch beim Vite-Container-Start ausgeführt

# 5. Datenbank migrieren
docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate --seed

# 6. Dateirechte für SQLite-Datenbank setzen (falls nötig)
docker compose -f docker-compose.dev.yaml exec php-fpm chown www-data:www-data database/database.sqlite
docker compose -f docker-compose.dev.yaml exec php-fpm chmod 664 database/database.sqlite

# 7. Application aufrufen
open http://localhost:8080
```

## Verfügbare Services

| Service        | URL                   | Beschreibung           |
| -------------- | --------------------- | ---------------------- |
| **Web**        | http://localhost:8080 | Laravel Application    |
| **Vite**       | http://localhost:5173 | Hot Module Replacement |
| **MailHog**    | http://localhost:8025 | Email Testing UI       |
| **PostgreSQL** | localhost:5432        | Datenbank              |
| **Redis**      | localhost:6379        | Cache & Queue          |

## Development Workflow

### Code-Änderungen

Alle Änderungen in PHP/Blade-Dateien werden **sofort** reflektiert (Volume-Mount).

Frontend-Assets (JS/CSS) nutzen **Vite HMR** für instant updates:

```bash
# Vite läuft automatisch im Container
docker compose -f docker-compose.dev.yaml logs -f vite
```

### Artisan Commands

```bash
# Migration erstellen
docker compose -f docker-compose.dev.yaml exec php-cli php artisan make:migration create_xyz_table

# Migration ausführen
docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate

# Tinker (REPL)
docker compose -f docker-compose.dev.yaml exec php-cli php artisan tinker

# Cache leeren
docker compose -f docker-compose.dev.yaml exec php-cli php artisan cache:clear
```

### Datenbank

```bash
# PostgreSQL CLI
docker compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev

# Datenbank resetten
docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate:fresh --seed
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
docker compose -f docker-compose.dev.yaml logs -f

# Nur PHP-FPM
docker compose -f docker-compose.dev.yaml logs -f php-fpm

# Laravel Log
docker compose -f docker-compose.dev.yaml exec php-cli tail -f storage/logs/laravel.log
```

### Email Testing mit MailHog

Alle von Laravel gesendeten Emails landen in MailHog:

1. Öffne http://localhost:8025
2. Sende Email via Laravel:
    ```php
    Mail::to('test@example.com')->send(new WelcomeMail());
    ```
3. Email erscheint sofort in MailHog UI

### npm Scripts

```bash
# Frontend Dependencies
docker compose -f docker-compose.dev.yaml exec vite npm install

# Build für Production
docker compose -f docker-compose.dev.yaml exec vite npm run build

# Vite dev server neu starten
docker compose -f docker-compose.dev.yaml restart vite
```

## Aliases (Optional)

Füge in `~/.bashrc` oder `~/.zshrc` hinzu:

```bash
alias ddev='docker compose -f docker-compose.dev.yaml'
alias dart='docker compose -f docker-compose.dev.yaml exec php-cli php artisan'
alias dcomposer='docker compose -f docker-compose.dev.yaml exec php-cli composer'
alias dnpm='docker compose -f docker-compose.dev.yaml exec vite npm'
```

Dann:

```bash
ddev up -d
dart migrate
dcomposer require vendor/package
dnpm install
```

## Troubleshooting

### Port bereits belegt

```bash
# Port 8080 belegt
# Ändere in docker-compose.dev.yaml: ports: "8081:80"

# PostgreSQL Port 5432 belegt
# Ändere in docker-compose.dev.yaml: ports: "5433:5432"
# Dann auch .env.dev: DB_PORT=5433
```

### Permission Errors

```bash
# Storage permissions
docker compose -f docker-compose.dev.yaml exec php-cli chmod -R 777 storage bootstrap/cache

# SQLite database permissions (bei "readonly database" Fehler)
docker compose -f docker-compose.dev.yaml exec php-fpm chown www-data:www-data database/database.sqlite
docker compose -f docker-compose.dev.yaml exec php-fpm chmod 664 database/database.sqlite

# Composer cache
docker compose -f docker-compose.dev.yaml exec php-cli composer clear-cache
```

### Container neustarten

```bash
# Alle Container
docker compose -f docker-compose.dev.yaml restart

# Nur php-fpm
docker compose -f docker-compose.dev.yaml restart php-fpm

# Rebuild bei Dockerfile-Änderungen
docker compose -f docker-compose.dev.yaml build --no-cache
docker compose -f docker-compose.dev.yaml up -d
```

### Volumes löschen

```bash
# Alle Dev-Volumes löschen (ACHTUNG: Daten gehen verloren!)
docker compose -f docker-compose.dev.yaml down -v

# Dann neu starten
docker compose -f docker-compose.dev.yaml up -d
docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate:fresh --seed
```

## Unterschiede zu Production

| Feature        | Development      | Production         |
| -------------- | ---------------- | ------------------ |
| **Debug**      | Xdebug aktiviert | Xdebug deaktiviert |
| **Errors**     | Angezeigt        | Geloggt            |
| **OPcache**    | Deaktiviert      | Aktiviert          |
| **Code Mount** | Live-Reload      | Baked in Image     |
| **Assets**     | Vite HMR         | Pre-built          |
| **Email**      | MailHog          | SMTP               |
| **Logs**       | Verbose          | Production-Level   |

## Best Practices

1. **Niemals** `.env.dev` committen (schon in `.gitignore`)
2. **Immer** `php artisan config:clear` nach `.env` Änderungen
3. **Regelmäßig** `composer update` für Dependencies
4. **Tests schreiben** vor Push: `php artisan test`
5. **Migrations** testen mit `migrate:fresh` vor Production-Deploy
