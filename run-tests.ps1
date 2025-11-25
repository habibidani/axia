# =============================================================================
# Axia Backend Test Suite Runner (Development Environment) - PowerShell
# =============================================================================
# Runs comprehensive test suite with PostgreSQL test database
#
# Usage:
#   .\run-tests.ps1                    # Run all tests
#   .\run-tests.ps1 -Filter Webhook    # Run specific test group
#   .\run-tests.ps1 -Coverage          # Run with coverage report
#
# =============================================================================

param(
    [string]$Filter = "",
    [switch]$Coverage = $false
)

$ErrorActionPreference = "Stop"

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Blue
Write-Host "║         AXIA BACKEND TEST SUITE (DEV ENV)                 ║" -ForegroundColor Blue
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Blue
Write-Host ""

# Check if containers are running
$containersRunning = docker-compose -f docker-compose.dev.yaml ps --format json | ConvertFrom-Json | Where-Object { $_.Service -eq "php-fpm" -and $_.State -eq "running" }

if (-not $containersRunning) {
    Write-Host "❌ Docker containers not running!" -ForegroundColor Red
    Write-Host "➜  Starting containers..." -ForegroundColor Yellow
    docker-compose -f docker-compose.dev.yaml up -d
    Start-Sleep -Seconds 5
}

Write-Host "✓ Docker containers running" -ForegroundColor Green
Write-Host ""

# Create test database if not exists
Write-Host "🗄️  Setting up test database..." -ForegroundColor Yellow
docker-compose -f docker-compose.dev.yaml exec -T postgres psql -U axia -d axia_dev -c "DROP DATABASE IF EXISTS axia_test;" 2>$null
docker-compose -f docker-compose.dev.yaml exec -T postgres psql -U axia -d axia_dev -c "CREATE DATABASE axia_test;" 2>$null
Write-Host "✓ Test database ready" -ForegroundColor Green
Write-Host ""

# Build test command
$testCmd = "php artisan test --parallel"

if ($Filter) {
    $testCmd += " --filter=$Filter"
}

if ($Coverage) {
    $testCmd += " --coverage --min=70"
}

# Run tests
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Blue
Write-Host "🧪 Running Test Suite" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Blue
Write-Host ""

$testResult = docker-compose -f docker-compose.dev.yaml exec -T php-fpm $testCmd

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
    Write-Host "✅ ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
    exit 0
} else {
    Write-Host ""
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Red
    Write-Host "❌ TESTS FAILED" -ForegroundColor Red
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Red
    exit 1
}
