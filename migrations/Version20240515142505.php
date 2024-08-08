<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240515142505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление связей между таблицами';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_GROUP_USER_GROUP FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_GROUP_USER_USER FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_GROUPS_SKILL FOREIGN KEY (skill_id) REFERENCES skills (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_USER_SKILL_USER FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_USER_SKILL_SKILL FOREIGN KEY (skill_id) REFERENCES skills (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_skill DROP CONSTRAINT FK_USER_SKILL_USER');
        $this->addSql('ALTER TABLE user_skill DROP CONSTRAINT FK_USER_SKILL_SKILL');

        $this->addSql('ALTER TABLE groups DROP CONSTRAINT FK_GROUPS_SKILL');

        $this->addSql('ALTER TABLE group_user DROP CONSTRAINT FK_GROUP_USER_GROUP');
        $this->addSql('ALTER TABLE group_user DROP CONSTRAINT FK_GROUP_USER_USER');
    }
}
