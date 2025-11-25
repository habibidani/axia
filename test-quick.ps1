# =============================================================================
# Quick Test Runner for Specific Test Suites
# =============================================================================

param(
    [Parameter(Position=0)]
    [ValidateSet("all", "relationships", "crud", "business", "services", "webhooks")]
    [string]$Suite = "all"
)

$filters = @{
    "all" = ""
    "relationships" = "ModelRelationships"
    "crud" = "ModelCrud"
    "business" = "BusinessLogic"
    "services" = "Service"
    "webhooks" = "Webhook"
}

$filter = $filters[$Suite]

Write-Host "🎯 Running test suite: $Suite" -ForegroundColor Cyan
Write-Host ""

if ($filter) {
    & ".\run-tests.ps1" -Filter $filter
} else {
    & ".\run-tests.ps1"
}
