<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127223819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ADD choice_id INT NOT NULL');
        $this->addSql('ALTER TABLE answer ADD question_id INT NOT NULL');
        $this->addSql('ALTER TABLE answer ALTER answer_session_id SET NOT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25998666D1 FOREIGN KEY (choice_id) REFERENCES choice (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_DADD4A25998666D1 ON answer (choice_id)');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
        $this->addSql('ALTER TABLE answer_session ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE answer_session ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE questionnaire ADD public_id UUID NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25998666D1');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF');
        $this->addSql('DROP INDEX IDX_DADD4A25998666D1');
        $this->addSql('DROP INDEX IDX_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE answer DROP choice_id');
        $this->addSql('ALTER TABLE answer DROP question_id');
        $this->addSql('ALTER TABLE answer ALTER answer_session_id DROP NOT NULL');
        $this->addSql('ALTER TABLE answer_session DROP email');
        $this->addSql('ALTER TABLE answer_session DROP status');
        $this->addSql('ALTER TABLE questionnaire DROP public_id');
    }
}
