<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240511115736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'hw4 Добавил пароль пользователя';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD password VARCHAR(120) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "users" DROP password');
    }
}
