<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206163803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    // migrations/VersionXXXXXXXXX.php
    public function up(Schema $schema): void
    {
        // Other schema changes...

        $this->addSql("INSERT INTO subscription_type (id, name) VALUES (1, 'subscription.free.title'), (2, 'subscription.premium.title')");
    }

    public function down(Schema $schema): void
    {
        // Other schema changes...

        $this->addSql("DELETE FROM subscription_type WHERE id IN (1, 2)");
    }

}
