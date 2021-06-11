<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210611204647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal ADD slug VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE legs legs INT NOT NULL, CHANGE can_it_fly can_it_fly TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE caretaker_animal DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE caretaker_animal ADD PRIMARY KEY (animal_id, caretaker_id)');
        $this->addSql('ALTER TABLE cage ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE caretaker ADD slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP slug, CHANGE description description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE legs legs INT DEFAULT 0 NOT NULL, CHANGE can_it_fly can_it_fly SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE cage DROP slug');
        $this->addSql('ALTER TABLE caretaker DROP slug');
        $this->addSql('ALTER TABLE caretaker_animal DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE caretaker_animal ADD PRIMARY KEY (caretaker_id, animal_id)');
    }
}
