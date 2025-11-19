#!/bin/bash

# Axia Development Environment Quick Start
# ========================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}"
echo "╔════════════════════════════════════════╗"
echo "║   Axia Development Environment Setup   ║"
echo "╚════════════════════════════════════════╝"
echo -e "${NC}"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}✗ Docker is not running. Please start Docker first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}"

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}→ Creating .env from .env.dev...${NC}"
    cp .env.dev .env
    echo -e "${GREEN}✓ .env created${NC}"
else
    echo -e "${YELLOW}→ .env already exists, skipping...${NC}"
fi

# Check if shared network exists
if ! docker network inspect axia-shared-network > /dev/null 2>&1; then
    echo -e "${YELLOW}→ Creating axia-shared-network...${NC}"
    docker network create axia-shared-network
    echo -e "${GREEN}✓ Network created${NC}"
else
    echo -e "${GREEN}✓ Network already exists${NC}"
fi

# Start containers
echo -e "${YELLOW}→ Starting Docker containers...${NC}"
docker compose -f docker-compose.dev.yaml up -d

# Wait for database
echo -e "${YELLOW}→ Waiting for database to be ready...${NC}"
sleep 10

# Generate app key if not exists
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}→ Generating application key...${NC}"
    docker compose -f docker-compose.dev.yaml exec php-cli php artisan key:generate
    echo -e "${GREEN}✓ Application key generated${NC}"
else
    echo -e "${GREEN}✓ Application key already exists${NC}"
fi

# Install composer dependencies
echo -e "${YELLOW}→ Installing Composer dependencies...${NC}"
docker compose -f docker-compose.dev.yaml exec php-cli composer install --no-interaction
echo -e "${GREEN}✓ Composer dependencies installed${NC}"

# Install npm dependencies
echo -e "${YELLOW}→ Installing NPM dependencies...${NC}"
docker compose -f docker-compose.dev.yaml exec vite npm install
echo -e "${GREEN}✓ NPM dependencies installed${NC}"

# Run migrations
echo -e "${YELLOW}→ Running database migrations...${NC}"
docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate:fresh --seed --force
echo -e "${GREEN}✓ Database migrated and seeded${NC}"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║         Setup Complete! 🎉             ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}Your Axia development environment is ready!${NC}"
echo ""
echo -e "Services running at:"
echo -e "  ${YELLOW}Application:${NC}  http://localhost:8080"
echo -e "  ${YELLOW}MailHog:${NC}      http://localhost:8025"
echo -e "  ${YELLOW}Vite HMR:${NC}     http://localhost:5173"
echo ""
echo -e "Useful commands:"
echo -e "  ${YELLOW}make dev-logs${NC}      - View container logs"
echo -e "  ${YELLOW}make migrate${NC}       - Run migrations"
echo -e "  ${YELLOW}make tinker${NC}        - Open Laravel Tinker"
echo -e "  ${YELLOW}make test${NC}          - Run tests"
echo -e "  ${YELLOW}make help${NC}          - Show all commands"
echo ""
echo -e "For more information, see ${YELLOW}DEV_SETUP.md${NC}"
echo ""
