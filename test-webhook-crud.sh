#!/bin/bash

echo "🧪 Running AXIA Webhook CRUD Tests..."
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Run the tests
echo "📦 Running Feature Tests..."
docker-compose -f docker-compose.dev.yaml exec php-fpm php artisan test --filter=WebhookPresetTest

# Check exit code
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ All Webhook CRUD tests passed!${NC}"
    echo ""
    
    # Additional manual tests
    echo "🔍 Running Manual Database Checks..."
    echo ""
    
    echo "1️⃣  Checking webhook_presets table structure..."
    docker-compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev -c "\d webhook_presets"
    
    echo ""
    echo "2️⃣  Checking users table for webhook fields..."
    docker-compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev -c "\d+ users" | grep webhook
    
    echo ""
    echo "3️⃣  Counting existing webhook presets..."
    docker-compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev -c "SELECT COUNT(*) as total_presets FROM webhook_presets;"
    
    echo ""
    echo -e "${GREEN}✅ All checks completed!${NC}"
else
    echo ""
    echo -e "${RED}❌ Some tests failed. Check the output above.${NC}"
    exit 1
fi
