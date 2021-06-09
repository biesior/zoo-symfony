<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20210525183904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE animal (
            id INT AUTO_INCREMENT NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            description TEXT, 
            legs INT NOT NULL DEFAULT 0,
            birth_date DATETIME NOT NULL, 
            can_it_fly SMALLINT NOT NULL DEFAULT 0, 
            cage_id INT DEFAULT NULL, 
            chat_token VARCHAR(255) NOT NULL, 
            INDEX IDX_6AAB231F5A70E5B7 (cage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE caretaker (
            id INT AUTO_INCREMENT NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            chat_token VARCHAR(255) DEFAULT NULL,
            chat_id VARCHAR(255) DEFAULT NULL, 
            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE caretaker_animal (caretaker_id INT NOT NULL, animal_id INT NOT NULL, INDEX IDX_764EB79E3F070B8B (caretaker_id), INDEX IDX_764EB79E8E962C16 (animal_id), PRIMARY KEY(caretaker_id, animal_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE animal_caretaker (animal_id INT NOT NULL, caretaker_id INT NOT NULL, INDEX IDX_764EB79E3F070B81 (caretaker_id), INDEX IDX_764EB79E8E962C11 (animal_id), PRIMARY KEY(animal_id, caretaker_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE animal_caretaker (animal_id INT NOT NULL, caretaker_id INT NOT NULL, PRIMARY KEY(animal_id, caretaker_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F5A70E5B7 FOREIGN KEY (cage_id) REFERENCES cage (id)');
        $this->addSql('ALTER TABLE caretaker_animal ADD CONSTRAINT FK_764EB79E3F070B8B FOREIGN KEY (caretaker_id) REFERENCES caretaker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE caretaker_animal ADD CONSTRAINT FK_764EB79E8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE CASCADE');
//        $this->addSql('ALTER TABLE animal_caretaker ADD CONSTRAINT FK_764EB79E3F070B81 FOREIGN KEY (caretaker_id) REFERENCES caretaker (id) ON DELETE CASCADE');
//        $this->addSql('ALTER TABLE animal_caretaker ADD CONSTRAINT FK_764EB79E8E962C11 FOREIGN KEY (animal_id) REFERENCES animal (id) ON DELETE CASCADE');

        // sample data
        $this->addSql("INSERT INTO cage (id, name) VALUES (1, 'Cage A')");
        $this->addSql("INSERT INTO cage (id, name) VALUES (2, 'Cage B')");
        $this->addSql("INSERT INTO cage (id, name) VALUES (3, 'Cage C')");
        $this->addSql("INSERT INTO cage (id, name) VALUES (4, 'Cage D')");
        $this->addSql("INSERT INTO cage (id, name) VALUES (5, 'Cage E')");

        $this->addSql("INSERT INTO animal (id, name, description, legs, birth_date, can_it_fly, cage_id) VALUES (1, 'Agatha', 'Retired circus seal', 4, '1975-05-08 21:37:37', 0, 1)");
        $this->addSql("INSERT INTO animal (id, name, description, legs, birth_date, can_it_fly, cage_id) VALUES (2, 'Leo Teo', 'King of the jungle', 4, '1998-05-08 12:14:07', 0, 2)");
        $this->addSql("INSERT INTO animal (id, name, description, legs, birth_date, can_it_fly, cage_id) VALUES (3, 'Floppy', 'Golden flying fish', 0, '2020-12-17 18:31:12', 1, 3)");
        $this->addSql("INSERT INTO animal (id, name, description, legs, birth_date, can_it_fly, cage_id) VALUES (4, 'Jumbo', 'Disney`s flying elephant', 4, '1945-03-27 09:01:53', 1, 4)");
        $this->addSql("INSERT INTO animal (id, name, description, legs, birth_date, can_it_fly, cage_id) VALUES (5, 'Mr. Shoe', 'A distinguished centipede', 100, '2021-04-19 08:12:27', 0, 5)");
        $this->addSql("INSERT INTO animal (id, name, description, legs, birth_date, can_it_fly, cage_id) VALUES (6, 'New Guy', 'Unknown species, we suspect that an <i>alien</i>.', 84, '2021-04-19 08:12:27', 1, null)");

        $this->addSql("INSERT INTO caretaker (id, name) VALUES (1, 'Mario')");
        $this->addSql("INSERT INTO caretaker (id, name) VALUES (2, 'Luigi')");
        $this->addSql("INSERT INTO caretaker (id, name) VALUES (3, 'Gianluca')");

        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (1, 1)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (2, 1)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (3, 2)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (1, 3)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (1, 4)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (2, 4)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (3, 4)");
        $this->addSql("INSERT INTO caretaker_animal (caretaker_id, animal_id) VALUES (1, 5)");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE caretaker_animal DROP FOREIGN KEY FK_764EB79E8E962C16');
//        $this->addSql('ALTER TABLE animal_caretaker DROP FOREIGN KEY FK_764EB79E8E962C11');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F5A70E5B7');
        $this->addSql('ALTER TABLE caretaker_animal DROP FOREIGN KEY FK_764EB79E3F070B8B');
//        $this->addSql('ALTER TABLE animal_caretaker DROP FOREIGN KEY FK_764EB79E3F070B81');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE cage');
        $this->addSql('DROP TABLE caretaker');
        $this->addSql('DROP TABLE caretaker_animal');
//        $this->addSql('DROP TABLE animal_caretaker');
    }
}
