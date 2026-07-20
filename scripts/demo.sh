#!/usr/bin/env bash
set -euo pipefail

API="${API:-http://localhost:8141/api/v1}"

echo "==> Health"
curl -s "$API/health" | jq .

echo "==> Login"
TOKEN=$(curl -s -X POST "$API/auth/login" \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@product.test","password":"password"}' | jq -r .token)
echo "token: ${TOKEN:0:24}..."

AUTH="Authorization: Bearer $TOKEN"

echo "==> List products"
curl -s "$API/products" -H "$AUTH" | jq .

echo "==> Place order (product 1, qty 1)"
curl -s -X POST "$API/orders" -H "$AUTH" -H 'Content-Type: application/json' \
  -d '{"product_id":1,"quantity":1}' | jq .

echo "==> Metrics snippet"
curl -s http://localhost:8141/metrics | head -20

echo "Done."
