<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240615144156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление поля password в таблицу users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD password VARCHAR(120) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP password');
    }
}
