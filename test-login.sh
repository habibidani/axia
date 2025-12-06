#!/bin/bash

BASE_URL="http://localhost:6478"

echo "=== Testing Login Flow ==="

# Step 1: Get login page (get CSRF token)
echo -e "\n1. Getting login page..."
RESPONSE=$(curl -s -c /tmp/cookies.txt -b /tmp/cookies.txt "$BASE_URL/login")
CSRF=$(echo "$RESPONSE" | grep -oP 'name="csrf" value="\K[^"]+' || echo "$RESPONSE" | grep -oP 'csrf_token.*?\K[^"]+')
echo "CSRF Token: $CSRF"

# Step 2: Try to login
echo -e "\n2. Attempting login..."
curl -s -c /tmp/cookies.txt -b /tmp/cookies.txt \
  -X POST "$BASE_URL/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=testuser@test.de&password=test1234&csrf=$CSRF" \
  -i 2>&1 | head -30

# Step 3: Check if authenticated
echo -e "\n3. Checking if authenticated (accessing /home)..."
curl -s -c /tmp/cookies.txt -b /tmp/cookies.txt \
  "$BASE_URL/home" \
  -i 2>&1 | head -20

# Step 4: Show cookies
echo -e "\n4. Saved Cookies:"
cat /tmp/cookies.txt
