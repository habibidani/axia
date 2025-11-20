#!/bin/bash

# MCP API Test Script
SECRET="vJHyD57OQ8LvXbLA7d/3NaazQNhyE/bs7XnNNfpHIa0="
BASE_URL="http://localhost:6478/api/internal/mcp"
USER_ID="019a9c72-aeff-72ca-9344-771cac3f08b6"

echo "======================================"
echo "   Axia MCP Internal API Tests"
echo "======================================"
echo ""

# Test 1: Health
echo "1. Testing Health Endpoint..."
curl -s -X GET "$BASE_URL/health" \
  -H "X-MCP-Secret: $SECRET" \
  -H "Accept: application/json" | jq .
echo ""

# Test 2: Get Context
echo "2. Testing Get User Context..."
curl -s -X POST "$BASE_URL/context" \
  -H "X-MCP-Secret: $SECRET" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"user_id\": \"$USER_ID\"}" | jq .
echo ""

# Test 3: Get Emails (placeholder)
echo "3. Testing Get IMAP Emails..."
curl -s -X POST "$BASE_URL/emails" \
  -H "X-MCP-Secret: $SECRET" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"user_id\": \"$USER_ID\", \"folder\": \"INBOX\", \"limit\": 10}" | jq .
echo ""

echo "======================================"
echo "   Tests Complete"
echo "======================================"
