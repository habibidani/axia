# ================================
# AXIA Webhook CRUD Test Script
# ================================

Write-Host "🧪 Running AXIA Webhook CRUD Tests..." -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Run the tests
Write-Host "📦 Running Feature Tests..." -ForegroundColor Yellow
docker-compose -f docker-compose.dev.yaml exec php-fpm php artisan test --filter=WebhookPresetTest

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ All Webhook CRUD tests passed!" -ForegroundColor Green
    Write-Host ""
    
    # Additional manual tests
    Write-Host "🔍 Running Manual Database Checks..." -ForegroundColor Yellow
    Write-Host ""
    
    Write-Host "1️⃣  Checking webhook_presets table structure..." -ForegroundColor Cyan
    docker-compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev -c "\d webhook_presets"
    
    Write-Host ""
    Write-Host "2️⃣  Checking users table for webhook fields..." -ForegroundColor Cyan
    docker-compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev -c "\d+ users" | Select-String "webhook"
    
    Write-Host ""
    Write-Host "3️⃣  Counting existing webhook presets..." -ForegroundColor Cyan
    docker-compose -f docker-compose.dev.yaml exec postgres psql -U axia -d axia_dev -c "SELECT COUNT(*) as total_presets FROM webhook_presets;"
    
    Write-Host ""
    Write-Host "✅ All checks completed!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "❌ Some tests failed. Check the output above." -ForegroundColor Red
    exit 1
}
