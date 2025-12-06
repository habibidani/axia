#!/usr/bin/env bash
# =============================================================================
# Axia Backend Test Suite Runner (Development Environment)
# =============================================================================
# Runs comprehensive test suite with PostgreSQL test database
#
# Usage:
#   ./run-tests.sh                    # Run all tests
#   ./run-tests.sh --filter=Webhook   # Run specific test group
#   ./run-tests.sh --coverage         # Run with coverage report
#
# =============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║         AXIA BACKEND TEST SUITE (DEV ENV)                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if containers are running
if ! docker-compose -f docker-compose.dev.yaml ps | grep -q "php-fpm.*Up"; then
    echo -e "${RED}❌ Docker containers not running!${NC}"
    echo -e "${YELLOW}➜  Starting containers...${NC}"
    docker-compose -f docker-compose.dev.yaml up -d
    sleep 5
fi

echo -e "${GREEN}✓ Docker containers running${NC}"
echo ""

# Create test database if not exists
echo -e "${YELLOW}🗄️  Setting up test database...${NC}"
docker-compose -f docker-compose.dev.yaml exec -T postgres psql -U axia -d axia_dev -c "DROP DATABASE IF EXISTS axia_test;" 2>/dev/null || true
docker-compose -f docker-compose.dev.yaml exec -T postgres psql -U axia -d axia_dev -c "CREATE DATABASE axia_test;" 2>/dev/null || true
echo -e "${GREEN}✓ Test database ready${NC}"
echo ""

# Parse arguments
FILTER=""
COVERAGE=false

for arg in "$@"; do
    case $arg in
        --filter=*)
            FILTER="${arg#*=}"
            shift
            ;;
        --coverage)
            COVERAGE=true
            shift
            ;;
    esac
done

# Build test command
TEST_CMD="php artisan test --parallel"

if [ -n "$FILTER" ]; then
    TEST_CMD="$TEST_CMD --filter=$FILTER"
fi

if [ "$COVERAGE" = true ]; then
    TEST_CMD="$TEST_CMD --coverage --min=70"
fi

# Run tests
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}🧪 Running Test Suite${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

if docker-compose -f docker-compose.dev.yaml exec -T php-fpm $TEST_CMD; then
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}✅ ALL TESTS PASSED!${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED}❌ TESTS FAILED${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    exit 1
fi
