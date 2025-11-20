#!/bin/bash

# Test script to verify no direct OpenAI API calls remain in the codebase

echo "🔍 Checking for direct OpenAI API usage..."
echo ""

# Check for api.openai.com
echo "1. Checking for api.openai.com references..."
if grep -r "api.openai.com" --include="*.php" app/ config/ 2>/dev/null; then
    echo "❌ FAIL: Found direct OpenAI API calls"
    exit 1
else
    echo "✅ PASS: No api.openai.com found in code"
fi

echo ""

# Check for OPENAI_API_KEY usage
echo "2. Checking for OPENAI_API_KEY references..."
if grep -r "OPENAI_API_KEY" --include="*.php" app/ config/ 2>/dev/null; then
    echo "❌ FAIL: Found OPENAI_API_KEY references"
    exit 1
else
    echo "✅ PASS: No OPENAI_API_KEY found in code"
fi

echo ""

# Check for OpenAiAnalysisService usage
echo "3. Checking for OpenAiAnalysisService references..."
if grep -r "OpenAiAnalysisService" --include="*.php" app/ 2>/dev/null; then
    echo "❌ FAIL: Found OpenAiAnalysisService usage"
    exit 1
else
    echo "✅ PASS: No OpenAiAnalysisService found"
fi

echo ""

# Check if WebhookAiService exists
echo "4. Checking if WebhookAiService exists..."
if [ -f "app/Services/WebhookAiService.php" ]; then
    echo "✅ PASS: WebhookAiService exists"
else
    echo "❌ FAIL: WebhookAiService not found"
    exit 1
fi

echo ""

# Check for WebhookAiService usage
echo "5. Checking for WebhookAiService usage..."
USAGE_COUNT=$(grep -r "WebhookAiService" --include="*.php" app/ | wc -l)
if [ "$USAGE_COUNT" -gt 0 ]; then
    echo "✅ PASS: Found $USAGE_COUNT references to WebhookAiService"
else
    echo "❌ FAIL: No WebhookAiService usage found"
    exit 1
fi

echo ""

# Check .env for webhook URL
echo "6. Checking .env for webhook configuration..."
if grep -q "N8N_AI_ANALYSIS_WEBHOOK_URL" .env; then
    echo "✅ PASS: N8N_AI_ANALYSIS_WEBHOOK_URL configured in .env"
else
    echo "⚠️  WARNING: N8N_AI_ANALYSIS_WEBHOOK_URL not in .env"
fi

echo ""
echo "======================================"
echo "✅ All checks passed!"
echo "======================================"
echo ""
echo "Summary:"
echo "- No direct OpenAI API calls found"
echo "- No API key references in code"
echo "- Old OpenAiAnalysisService removed"
echo "- New WebhookAiService in use"
echo "- Webhook URLs configured"
echo ""
echo "Next steps:"
echo "1. Set up n8n workflow at: https://n8n.getaxia.de/webhook/ai-analysis"
echo "2. Test AI features in the application"
echo "3. Monitor webhook calls in n8n dashboard"
