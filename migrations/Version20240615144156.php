<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240615144156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'hw4 doctrine переименовывает ключи автоматически при diff, пока оставил как есть';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_user DROP CONSTRAINT group_user__group_id_fk');
        $this->addSql('ALTER TABLE group_user DROP CONSTRAINT group_user__user_id_fk');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39FE54D947 FOREIGN KEY (group_id) REFERENCES "groups" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT groups__skill_id_fk');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D39705585C142 FOREIGN KEY (skill_id) REFERENCES "skills" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_skill DROP CONSTRAINT user_skill__user_id_fk');
        $this->addSql('ALTER TABLE user_skill DROP CONSTRAINT user_skill__skill_id_fk');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2FA76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2F5585C142 FOREIGN KEY (skill_id) REFERENCES "skills" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user_skill" DROP CONSTRAINT FK_BCFF1F2FA76ED395');
        $this->addSql('ALTER TABLE "user_skill" DROP CONSTRAINT FK_BCFF1F2F5585C142');
        $this->addSql('ALTER TABLE "user_skill" ADD CONSTRAINT user_skill__user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_skill" ADD CONSTRAINT user_skill__skill_id_fk FOREIGN KEY (skill_id) REFERENCES skills (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group_user" DROP CONSTRAINT FK_A4C98D39FE54D947');
        $this->addSql('ALTER TABLE "group_user" DROP CONSTRAINT FK_A4C98D39A76ED395');
        $this->addSql('ALTER TABLE "group_user" ADD CONSTRAINT group_user__group_id_fk FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group_user" ADD CONSTRAINT group_user__user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "groups" DROP CONSTRAINT FK_F06D39705585C142');
        $this->addSql('ALTER TABLE "groups" ADD CONSTRAINT groups__skill_id_fk FOREIGN KEY (skill_id) REFERENCES skills (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
