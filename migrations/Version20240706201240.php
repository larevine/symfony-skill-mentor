<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240706201240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление поля token в таблицу users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD token VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX users__token__uq ON users (token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX users__token__uq');
        $this->addSql('ALTER TABLE users DROP token');
    }
}
