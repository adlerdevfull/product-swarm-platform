#!/usr/bin/env bash
set -euo pipefail

STACK="${STACK_NAME:-product}"
REPLICAS="${1:-5}"

echo "Scaling ${STACK}_app to $REPLICAS replicas..."
docker service scale "${STACK}_app=$REPLICAS"
docker service ps "${STACK}_app"
