#!/usr/bin/env bash
set -euo pipefail

# Production deployment script for Axia
PROJECT_DIR="/home/nileneb/axia"
AXIA_COMPOSE="docker-compose.yaml"
N8N_COMPOSE="docker-compose.n8n.yaml"

cd "$PROJECT_DIR"

echo ">>> Building & starting Axia production stack..."
docker compose -f "$AXIA_COMPOSE" pull || true
docker compose -f "$AXIA_COMPOSE" build
docker compose -f "$AXIA_COMPOSE" up -d

echo ">>> Building & starting N8N stack..."
docker compose -f "$N8N_COMPOSE" pull || true
docker compose -f "$N8N_COMPOSE" build
docker compose -f "$N8N_COMPOSE" up -d

echo ">>> Waiting for services to be ready..."
sleep 5

echo ">>> Building frontend assets (Vite)..."
npm run build

echo ">>> Running Laravel setup tasks inside php-cli container..."

# APP_KEY generieren (falls leer)
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan key:generate --force

# Run migrations (nicht fresh, um keine Sessions zu löschen!)
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan migrate --force

# Clear old caches first
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan config:clear
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan route:clear
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan cache:clear

# Optimize caches
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan config:cache
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan route:cache
docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan view:cache

# Optional: Run seeders if needed
# docker compose -f "$AXIA_COMPOSE" exec -T php-cli php artisan db:seed --force

echo ">>> Axia production deployment finished."
echo ""
echo ">>> Running containers:"
docker compose -f "$AXIA_COMPOSE" ps
echo ""
docker compose -f "$N8N_COMPOSE" ps
echo ""
echo ">>> URLs:"
echo "    -> Axia:  http://localhost"
echo "    -> N8N:   http://localhost:5678"
