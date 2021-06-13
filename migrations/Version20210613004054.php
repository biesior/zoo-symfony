<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210613004054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE caretaker ADD username VARCHAR(180) NOT NULL, 
                            ADD roles JSON NOT NULL, 
                            ADD password VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE caretaker SET username=id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D1A32B5BD44C0494 ON caretaker (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D1A32B5BD44C0494 ON caretaker');
        $this->addSql('ALTER TABLE caretaker DROP username, DROP roles, DROP password');
    }
}
