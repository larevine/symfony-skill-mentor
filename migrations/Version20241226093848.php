<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241226093848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Правки';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT FK_F06D397041807E1D');
        $this->addSql('ALTER TABLE groups ALTER teacher_id DROP NOT NULL');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D397041807E1D FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_proficiencies DROP CONSTRAINT FK_FD1AE76A41807E1D');
        $this->addSql('ALTER TABLE skill_proficiencies DROP CONSTRAINT FK_FD1AE76ACB944F1A');
        $this->addSql('ALTER TABLE skill_proficiencies DROP CONSTRAINT FK_FD1AE76AFE54D947');
        $this->addSql('ALTER TABLE skill_proficiencies ADD CONSTRAINT FK_FD1AE76A41807E1D FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_proficiencies ADD CONSTRAINT FK_FD1AE76ACB944F1A FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_proficiencies ADD CONSTRAINT FK_FD1AE76AFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill_proficiencies DROP CONSTRAINT fk_fd1ae76a41807e1d');
        $this->addSql('ALTER TABLE skill_proficiencies DROP CONSTRAINT fk_fd1ae76acb944f1a');
        $this->addSql('ALTER TABLE skill_proficiencies DROP CONSTRAINT fk_fd1ae76afe54d947');
        $this->addSql('ALTER TABLE skill_proficiencies ADD CONSTRAINT fk_fd1ae76a41807e1d FOREIGN KEY (teacher_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_proficiencies ADD CONSTRAINT fk_fd1ae76acb944f1a FOREIGN KEY (student_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_proficiencies ADD CONSTRAINT fk_fd1ae76afe54d947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT fk_f06d397041807e1d');
        $this->addSql('ALTER TABLE groups ALTER teacher_id SET NOT NULL');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT fk_f06d397041807e1d FOREIGN KEY (teacher_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
