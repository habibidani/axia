#!/bin/bash

# Test script for Axia MCP Server
# This tests the MCP server by sending it MCP protocol messages via stdin

set -e

echo "=== Testing Axia MCP Server ==="

# Test 1: List available tools
echo ""
echo "Test 1: Listing available tools..."
echo '{"jsonrpc":"2.0","method":"tools/list","id":1}' | \
  docker compose -f docker-compose.n8n.yaml exec -T mcp-axia node index.js 2>&1 | \
  grep -v "Axia MCP Server running" | \
  jq -r '.result.tools[] | "  - \(.name): \(.description)"'

# Test 2: List available resources
echo ""
echo "Test 2: Listing available resources..."
echo '{"jsonrpc":"2.0","method":"resources/list","id":2}' | \
  docker compose -f docker-compose.n8n.yaml exec -T mcp-axia node index.js 2>&1 | \
  grep -v "Axia MCP Server running" | \
  jq -r '.result.resources[] | "  - \(.name): \(.description)"'

# Test 3: Call get_user tool
echo ""
echo "Test 3: Calling get_user tool..."
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"get_user","arguments":{"include_company":true}},"id":3}' | \
  docker compose -f docker-compose.n8n.yaml exec -T mcp-axia node index.js 2>&1 | \
  grep -v "Axia MCP Server running" | \
  jq -r '.result.content[0].text' | \
  jq -r '.data | "  User ID: \(.id)\n  Company: \(.name)\n  Goals: \(.goals | length)"'

# Test 4: Call get_goals tool
echo ""
echo "Test 4: Calling get_goals tool..."
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"get_goals","arguments":{"include_kpis":true}},"id":4}' | \
  docker compose -f docker-compose.n8n.yaml exec -T mcp-axia node index.js 2>&1 | \
  grep -v "Axia MCP Server running" | \
  jq -r '.result.content[0].text'

echo ""
echo "=== All Tests Passed! ✅ ==="
