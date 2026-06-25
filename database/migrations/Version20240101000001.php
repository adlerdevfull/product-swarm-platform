<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(120) NOT NULL,
            roles JSON NOT NULL,
            UNIQUE INDEX UNIQ_USERS_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE products (
            id INT AUTO_INCREMENT NOT NULL,
            sku VARCHAR(64) NOT NULL,
            name VARCHAR(200) NOT NULL,
            description LONGTEXT NOT NULL,
            price_cents INT NOT NULL,
            stock INT NOT NULL,
            status VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_PRODUCTS_SKU (sku),
            INDEX IDX_PRODUCTS_STATUS (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE orders (
            id INT AUTO_INCREMENT NOT NULL,
            order_number VARCHAR(32) NOT NULL,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            product_sku VARCHAR(64) NOT NULL,
            quantity INT NOT NULL,
            unit_price_cents INT NOT NULL,
            total_cents INT NOT NULL,
            status VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_ORDERS_NUMBER (order_number),
            INDEX IDX_ORDERS_USER (user_id),
            INDEX IDX_ORDERS_STATUS (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE users');
    }
}
