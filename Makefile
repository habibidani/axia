.PHONY: help dev-up dev-down dev-restart dev-logs dev-build dev-clean

# Colors for output
GREEN  := \033[0;32m
YELLOW := \033[0;33m
NC     := \033[0m # No Color

help: ## Show this help message
	@echo '$(GREEN)Axia Development Commands$(NC)'
	@echo ''
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ''

# Development Environment
dev-up: ## Start development environment
	docker compose -f docker-compose.dev.yaml up -d
	@echo "$(GREEN)✓ Development environment started$(NC)"
	@echo "  Web:     http://localhost:8080"
	@echo "  MailHog: http://localhost:8025"
	@echo "  Vite:    http://localhost:5173"

dev-down: ## Stop development environment
	docker compose -f docker-compose.dev.yaml down
	@echo "$(GREEN)✓ Development environment stopped$(NC)"

dev-restart: ## Restart development environment
	docker compose -f docker-compose.dev.yaml restart
	@echo "$(GREEN)✓ Development environment restarted$(NC)"

dev-logs: ## Show logs from all containers
	docker compose -f docker-compose.dev.yaml logs -f

dev-build: ## Rebuild development containers
	docker compose -f docker-compose.dev.yaml build --no-cache
	@echo "$(GREEN)✓ Containers rebuilt$(NC)"

dev-clean: ## Remove all development containers and volumes
	docker compose -f docker-compose.dev.yaml down -v
	@echo "$(YELLOW)⚠ All development data removed$(NC)"

# Laravel Commands
artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan $(cmd)

tinker: ## Open Laravel Tinker REPL
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan tinker

migrate: ## Run database migrations
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate
	@echo "$(GREEN)✓ Migrations completed$(NC)"

migrate-fresh: ## Reset database and run migrations with seeders
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate:fresh --seed
	@echo "$(GREEN)✓ Database reset and seeded$(NC)"

cache-clear: ## Clear all Laravel caches
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan cache:clear
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan config:clear
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan route:clear
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan view:clear
	@echo "$(GREEN)✓ All caches cleared$(NC)"

# Composer & NPM
composer: ## Run composer command (usage: make composer cmd="install")
	docker compose -f docker-compose.dev.yaml exec php-cli composer $(cmd)

composer-install: ## Install PHP dependencies
	docker compose -f docker-compose.dev.yaml exec php-cli composer install
	@echo "$(GREEN)✓ Composer dependencies installed$(NC)"

npm: ## Run npm command (usage: make npm cmd="install")
	docker compose -f docker-compose.dev.yaml exec vite npm $(cmd)

npm-install: ## Install Node dependencies
	docker compose -f docker-compose.dev.yaml exec vite npm install
	@echo "$(GREEN)✓ NPM dependencies installed$(NC)"

npm-build: ## Build production assets
	docker compose -f docker-compose.dev.yaml exec vite npm run build
	@echo "$(GREEN)✓ Assets built$(NC)"

# Testing
test: ## Run PHPUnit tests
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan test

test-coverage: ## Run tests with coverage report
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan test --coverage

# Database
db-shell: ## Open PostgreSQL shell
	docker compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev

db-backup: ## Backup development database
	docker compose -f docker-compose.dev.yaml exec postgres pg_dump -U axia axia_dev > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✓ Database backed up$(NC)"

# Production Commands
prod-up: ## Start production environment
	docker compose up -d
	@echo "$(GREEN)✓ Production environment started$(NC)"

prod-down: ## Stop production environment
	docker compose down
	@echo "$(GREEN)✓ Production environment stopped$(NC)"

prod-logs: ## Show production logs
	docker compose logs -f

# Setup
init: ## Initialize project (first time setup)
	@echo "$(GREEN)Initializing Axia development environment...$(NC)"
	cp .env.dev .env
	docker compose -f docker-compose.dev.yaml up -d
	sleep 10
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan key:generate
	docker compose -f docker-compose.dev.yaml exec php-cli composer install
	docker compose -f docker-compose.dev.yaml exec vite npm install
	docker compose -f docker-compose.dev.yaml exec php-cli php artisan migrate:fresh --seed
	@echo "$(GREEN)✓ Project initialized successfully!$(NC)"
	@echo "  Visit: http://localhost:8080"

init-prod: ## Initialize production environment
	@echo "$(GREEN)Initializing Axia production environment...$(NC)"
	docker compose up -d
	sleep 10
	docker compose exec php-cli php artisan migrate --force
	@echo "$(GREEN)✓ Production environment initialized!$(NC)"
