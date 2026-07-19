# Product Swarm Platform


> **Languages / Idiomas / Idiomas:** [English](#-english) · [Español](#-español) · [Português](#-português)

Digital product catalog & orders platform built for **product engineering + DevOps**: Symfony hexagonal backend, Vue 3 frontend, MySQL, Redis, RabbitMQ, **Docker Swarm**, **Prometheus** and **Grafana**.

Aligned with a typical European senior backend role: PHP/Symfony, SOLID/TDD, Agile delivery, SQL, Redis/RabbitMQ, and infrastructure with Docker + Swarm and monitoring.

---

## 🇬🇧 English

### What this project is

A **product platform** where you manage catalog items (SKU, stock, publish lifecycle) and place orders with stock reservation, state machine transitions, and async notifications via RabbitMQ. The same codebase runs:

| Mode | File | Purpose |
|------|------|---------|
| Local dev | `docker-compose.yml` | 1 app + worker + full observability stack |
| Swarm | `docker-stack.yml` | Replicated app/worker/nginx, overlay networks, rolling updates |

### Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2 + Symfony 7 + Doctrine ORM |
| Frontend | Vue 3 + TypeScript + Vite + TailwindCSS |
| Database | MySQL 8 |
| Cache / metrics store | Redis 7 |
| Messaging | RabbitMQ 3 (topic exchange) |
| Auth | JWT (`lexik/jwt-authentication-bundle`) |
| Orchestration | Docker Compose (dev) · **Docker Swarm** (prod-like) |
| Monitoring | Prometheus + Grafana (+ alert rules) |
| CI | Bitbucket Pipelines style + GitHub Actions sample |
| Architecture | Hexagonal + DDD + SOLID |

### Architecture

```
src/
├── Domain/                 # Pure business rules (no framework)
│   ├── Product/            # Catalog, stock, publish lifecycle
│   ├── Order/              # Place / confirm / ship / deliver / cancel
│   └── Shared/             # Money value object
├── Application/            # Use-case handlers
└── Infrastructure/         # Doctrine, HTTP, RabbitMQ, Prometheus metrics
```

```
                    ┌─────────────┐
                    │   Clients   │
                    └──────┬──────┘
                           │
              ┌────────────┴────────────┐
              │  nginx (ingress / VIP)  │  Swarm service
              └────────────┬────────────┘
                     ┌─────┴─────┐
                     │ app × N   │  replicas (stateless JWT)
                     └─────┬─────┘
           ┌───────────────┼───────────────┐
           ▼               ▼               ▼
        MySQL            Redis          RabbitMQ
                                      (workers × M)
           │
     Prometheus ──► Grafana
```

### Domain flows

**Product:** `draft` → `published` → `archived` (also `published` → `draft`)

**Order:** `pending` → `confirmed` → `shipped` → `delivered`  
Also: `pending|confirmed` → `cancelled` (stock released)

### Delivery phases

This repository was built in progressive phases (see commit history):

| Phase | Focus |
|-------|--------|
| 1. Scaffold | Bootstrap Symfony project and ignore rules |
| 2. Domain | Entities, enums and repository ports (pure PHP) |
| 3. Application | Command handlers / use cases |
| 4. Infrastructure | Doctrine models and repository adapters |
| 5. API & security | HTTP controllers, JWT and config |
| 6. Database | Migrations and seed data |
| 7. Messaging | RabbitMQ publishers and workers (when applicable) |
| 8. Docker | Local Docker Compose stack |
| 9. Frontend tooling | Vue 3 + TypeScript + Vite scaffold |
| 10. Frontend UI | Application pages and API client |
| 11. Tests | Domain unit tests |
| 12. Docs & ops | Multi-language README, CI, Swarm/monitoring (when applicable) |
| 13. Swarm stack | docker-stack.yml with replicas, rolling update and overlay networks |
| 14. Observability | Prometheus scrape, Grafana dashboards and alert rules |

### How to run (local)

```bash
cd product-swarm-platform
cp .env.example .env
docker compose up -d --build
```

Wait until the app entrypoint finishes Composer + migrations, then:

| Service | URL |
|---------|-----|
| API | http://localhost:8141/api/v1 |
| Frontend | http://localhost:3009 |
| Prometheus | http://localhost:9090 |
| Grafana | http://localhost:3000 (`admin` / `admin`) |
| RabbitMQ UI | http://localhost:15675 (`guest` / `guest`) |
| Metrics | http://localhost:8141/metrics |

**Login:** `admin@product.test` / `password`

```bash
# Demo API flow
chmod +x scripts/demo.sh && ./scripts/demo.sh

# Unit tests (inside container after composer install)
docker compose exec app ./vendor/bin/phpunit
```

### How to run (Docker Swarm)

```bash
chmod +x scripts/swarm-deploy.sh scripts/swarm-scale.sh
./scripts/swarm-deploy.sh

# Scale application replicas
./scripts/swarm-scale.sh 5

# Inspect
docker stack services product
docker service ps product_app
docker service logs -f product_app
```

Useful Swarm concepts demonstrated:

- **Replicas** on `app`, `worker`, `nginx`
- **Overlay networks** (`frontend` public, `backend` internal)
- **Rolling update** with `order: start-first` and **automatic rollback**
- **Resource limits** and **placement constraints** (DB/monitoring on manager)
- **Healthchecks** for PHP-FPM
- **Configs** for nginx + Prometheus (Swarm configs)

### API overview

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/v1/health` | no | Liveness + instance id |
| GET | `/api/v1/ready` | no | Readiness probe |
| POST | `/api/v1/auth/login` | no | JWT login |
| GET | `/api/v1/auth/me` | yes | Current user |
| GET/POST | `/api/v1/products` | yes | List / create |
| POST | `/api/v1/products/{id}/publish` | yes | Publish product |
| GET/POST | `/api/v1/orders` | yes | List / place |
| POST | `/api/v1/orders/{id}/transition` | yes | `{ "status": "confirmed" }` |
| GET | `/metrics` | no | Prometheus exposition |

### Tests

```bash
docker compose exec app ./vendor/bin/phpunit
# or locally after composer install:
composer install && ./vendor/bin/phpunit
```

---

## 🇪🇸 Español

### Qué es este proyecto

Plataforma de **producto digital** (catálogo + pedidos) pensada para un rol de backend de producto con mentalidad DevOps: Symfony hexagonal, Vue 3, MySQL, Redis, RabbitMQ, **Docker Swarm**, **Prometheus** y **Grafana**.

### Stack

- **Backend:** PHP 8.2 + Symfony 7 + Doctrine
- **Frontend:** Vue 3 + TypeScript + Vite + Tailwind
- **Datos:** MySQL 8 · Redis 7 · RabbitMQ 3
- **Auth:** JWT
- **Orquestación:** Docker Compose (local) · **Docker Swarm** (escalado)
- **Observabilidad:** Prometheus + Grafana + alertas
- **CI:** ejemplo Bitbucket Pipelines
- **Arquitectura:** Hexagonal + DDD + SOLID

### Fases de entrega

Este repositorio se construyó en fases progresivas (ver historial de commits):

| Fase | Enfoque |
|-------|--------|
| 1. Scaffold | Bootstrap del proyecto Symfony e ignore rules |
| 2. Dominio | Entidades, enums e interfaces de repositorio (PHP puro) |
| 3. Aplicación | Command handlers / casos de uso |
| 4. Infraestructura | Models Doctrine y adapters de repositorio |
| 5. API y seguridad | Controllers HTTP, JWT y configuración |
| 6. Base de datos | Migraciones y datos seed |
| 7. Mensajería | Publishers y workers RabbitMQ (cuando aplique) |
| 8. Docker | Stack Docker Compose local |
| 9. Frontend tooling | Scaffold Vue 3 + TypeScript + Vite |
| 10. Frontend UI | Páginas de la aplicación y cliente API |
| 11. Tests | Tests unitarios de dominio |
| 12. Docs & ops | README multi-idioma, CI, Swarm/monitoring (cuando aplique) |
| 13. Stack Swarm | docker-stack.yml con réplicas, rolling update y redes overlay |
| 14. Observabilidad | Scrape Prometheus, dashboards Grafana y alertas |

### Cómo ejecutar (local)

```bash
cd product-swarm-platform
cp .env.example .env
docker compose up -d --build
```

- Frontend: http://localhost:3009  
- API: http://localhost:8141/api/v1  
- Prometheus: http://localhost:9090  
- Grafana: http://localhost:3000 (`admin` / `admin`)  
- RabbitMQ: http://localhost:15675  

**Login:** `admin@product.test` / `password`

### Cómo ejecutar (Swarm)

```bash
./scripts/swarm-deploy.sh
./scripts/swarm-scale.sh 5
docker stack services product
```

### Flujos de dominio

**Producto:** `draft` → `published` → `archived`  

**Pedido:** `pending` → `confirmed` → `shipped` → `delivered` (o `cancelled` con devolución de stock)

### Por qué Swarm y no solo Compose

Compose es ideal para desarrollo en una máquina. **Swarm** añade orquestación real: réplicas, redes overlay multi-nodo, rolling updates con rollback, límites de recursos y service discovery DNS. Es el plus que piden muchas ofertas europeas junto a Prometheus/Grafana.

### Tests

```bash
docker compose exec app ./vendor/bin/phpunit
```

---

## 🇧🇷 Português

### O que é este projeto

Plataforma de **produto digital** (catálogo + pedidos) alinhada a vagas de backend com foco em produto e DevOps: Symfony hexagonal, Vue 3, MySQL, Redis, RabbitMQ, **Docker Swarm**, **Prometheus** e **Grafana**.

### Stack

- **Backend:** PHP 8.2 + Symfony 7 + Doctrine  
- **Frontend:** Vue 3 + TypeScript + Vite + Tailwind  
- **Dados:** MySQL 8 · Redis 7 · RabbitMQ 3  
- **Auth:** JWT  
- **Orquestração:** Docker Compose (local) · **Docker Swarm** (produção simulada)  
- **Observabilidade:** Prometheus + Grafana + alertas  
- **CI:** Bitbucket Pipelines (estilo da vaga)  
- **Arquitetura:** Hexagonal + DDD + SOLID  

### Fases de entrega

Este repositório foi construído em fases progressivas (ver histórico de commits):

| Fase | Foco |
|-------|--------|
| 1. Scaffold | Bootstrap do projeto Symfony e ignore rules |
| 2. Domínio | Entidades, enums e portas de repositório (PHP puro) |
| 3. Aplicação | Command handlers / casos de uso |
| 4. Infraestrutura | Models Doctrine e adapters de repositório |
| 5. API e segurança | Controllers HTTP, JWT e configuração |
| 6. Banco de dados | Migrations e dados seed |
| 7. Mensageria | Publishers e workers RabbitMQ (quando aplicável) |
| 8. Docker | Stack Docker Compose local |
| 9. Frontend tooling | Scaffold Vue 3 + TypeScript + Vite |
| 10. Frontend UI | Páginas da aplicação e cliente da API |
| 11. Testes | Testes unitários de domínio |
| 12. Docs & ops | README multi-idioma, CI, Swarm/monitoring (quando aplicável) |
| 13. Stack Swarm | docker-stack.yml com réplicas, rolling update e redes overlay |
| 14. Observabilidade | Scrape Prometheus, dashboards Grafana e alertas |

### Como executar (local)

```bash
cd product-swarm-platform
cp .env.example .env
docker compose up -d --build
```

| Serviço | URL |
|---------|-----|
| API | http://localhost:8141/api/v1 |
| Frontend | http://localhost:3009 |
| Prometheus | http://localhost:9090 |
| Grafana | http://localhost:3000 (`admin` / `admin`) |
| RabbitMQ | http://localhost:15675 |

**Login:** `admin@product.test` / `password`

Demo rápida:

```bash
./scripts/demo.sh
```

### Como executar (Docker Swarm)

```bash
./scripts/swarm-deploy.sh          # init swarm + build + stack deploy
./scripts/swarm-scale.sh 5         # escala o serviço app
docker stack services product
docker service logs -f product_app
```

### Fluxos de domínio

**Produto:** `draft` → `published` → `archived`  

**Pedido:** `pending` → `confirmed` → `shipped` → `delivered`  
Cancelamento (`cancelled`) devolve stock.

### Arquitetura em camadas

```
Domain/         → regras puras (Product, Order, Money)
Application/    → handlers (criar produto, publicar, fazer pedido)
Infrastructure/ → Doctrine, controllers HTTP, RabbitMQ, métricas Prometheus
```

O domínio **não** depende do Symfony. Controllers e repositórios Doctrine são adapters.

### Observabilidade

1. A API incrementa contadores em Redis (`http_requests_total`, `orders_placed_total`, …).  
2. `GET /metrics` expõe o formato texto do Prometheus.  
3. Prometheus scrapa o nginx/app a cada 15s.  
4. Grafana já vem com datasource e dashboard **Product Swarm Platform**.  
5. Regras em `monitoring/alerts.yml` (target down, memória alta, sem tráfego).

### CI

- `bitbucket-pipelines.yml` — estilo da vaga (tests + build da imagem)  
- `.github/workflows/ci.yml` — alternativa GitHub Actions  

### Testes

```bash
docker compose exec app ./vendor/bin/phpunit
```

---

## Project layout

```
product-swarm-platform/
├── docker-compose.yml      # local full stack
├── docker-stack.yml        # Swarm stack
├── docker/                 # Dockerfile, nginx, grafana provisioning
├── monitoring/             # prometheus + alerts
├── scripts/                # demo, swarm-deploy, swarm-scale
├── src/Domain|Application|Infrastructure
├── frontend/               # Vue 3 + TS
├── tests/Unit/
├── bitbucket-pipelines.yml
└── README.md               # this file (EN / ES / PT)
```

## License

MIT — study / interview portfolio project.
