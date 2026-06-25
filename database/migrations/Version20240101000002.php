<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Seed admin user + sample products (password: password). */
final class Version20240101000002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // bcrypt hash of "password"
        $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $this->addSql("INSERT INTO users (email, password, name, roles) VALUES
            ('admin@product.test', '{$hash}', 'Admin User', '[\"ROLE_ADMIN\"]')");

        $this->addSql("INSERT INTO products (sku, name, description, price_cents, stock, status, created_at) VALUES
            ('SKU-API-001', 'API Gateway Pro', 'Managed API gateway with rate limiting', 49900, 100, 'published', NOW()),
            ('SKU-OBS-002', 'Observability Pack', 'Prometheus + Grafana starter kit', 29900, 50, 'published', NOW()),
            ('SKU-SWARM-003', 'Swarm Ops Course', 'Docker Swarm hands-on training', 14900, 200, 'draft', NOW())");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM products WHERE sku IN ('SKU-API-001','SKU-OBS-002','SKU-SWARM-003')");
        $this->addSql("DELETE FROM users WHERE email = 'admin@product.test'");
    }
}
