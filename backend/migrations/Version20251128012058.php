<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128012058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD is_root BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE questionnaire DROP CONSTRAINT fk_7a64daf3980f4b2');
        $this->addSql('DROP INDEX idx_7a64daf3980f4b2');
        $this->addSql('ALTER TABLE questionnaire DROP root_question_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP is_root');
        $this->addSql('ALTER TABLE questionnaire ADD root_question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT fk_7a64daf3980f4b2 FOREIGN KEY (root_question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_7a64daf3980f4b2 ON questionnaire (root_question_id)');
    }
}
