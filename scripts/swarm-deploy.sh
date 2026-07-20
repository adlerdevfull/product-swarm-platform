#!/usr/bin/env bash
# Deploy Product Platform on Docker Swarm
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

IMAGE="${PRODUCT_IMAGE:-product-platform:latest}"
STACK="${STACK_NAME:-product}"

echo "==> Ensuring Swarm mode"
if ! docker info 2>/dev/null | grep -q 'Swarm: active'; then
  docker swarm init
fi

echo "==> Building image: $IMAGE"
docker build -t "$IMAGE" -f docker/Dockerfile --target production .

echo "==> Deploying stack: $STACK"
export PRODUCT_IMAGE="$IMAGE"
docker stack deploy -c docker-stack.yml "$STACK"

echo "==> Services"
docker stack services "$STACK"

echo ""
echo "API:        http://localhost:8141/api/v1/health"
echo "Prometheus: http://localhost:9090"
echo "Grafana:    http://localhost:3000 (admin/admin)"
echo "RabbitMQ:   http://localhost:15675 (guest/guest)"
echo ""
echo "Scale:  docker service scale ${STACK}_app=5"
echo "Logs:   docker service logs -f ${STACK}_app"
