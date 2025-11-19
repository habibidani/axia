#!/bin/bash

# Axia Chat Test Script
# Tests the n8n chat webhook integration

echo "🧪 Testing Axia Chat Integration..."
echo ""

# Check if N8N_CHAT_WEBHOOK_URL is set
if [ -z "$N8N_CHAT_WEBHOOK_URL" ]; then
    WEBHOOK_URL="https://n8n.getaxia.de/webhook/chat"
    echo "⚠️  N8N_CHAT_WEBHOOK_URL not set, using default: $WEBHOOK_URL"
else
    WEBHOOK_URL="$N8N_CHAT_WEBHOOK_URL"
    echo "✅ Using webhook URL from .env: $WEBHOOK_URL"
fi

echo ""
echo "📤 Sending test message..."
echo ""

RESPONSE=$(curl -s -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "What are my current goals?",
    "sessionId": "test-session-'$(date +%s)'",
    "metadata": {
      "userId": "test-user",
      "userName": "Test User",
      "userEmail": "test@example.com",
      "companyId": "test-company"
    }
  }')

echo "📥 Response:"
echo "$RESPONSE" | jq . 2>/dev/null || echo "$RESPONSE"

echo ""
echo "✅ Test complete!"
echo ""
echo "Expected response:"
echo "- Should have 'response' field with AI generated text"
echo "- Should have 'sessionId' field"
echo ""
echo "If error, check:"
echo "1. n8n workflow is active"
echo "2. OpenAI credentials are set"
echo "3. Axia MCP server is running (docker logs mcp-axia)"
