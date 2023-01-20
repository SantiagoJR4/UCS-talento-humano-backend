<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230119233213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE further_training (id INT UNSIGNED AUTO_INCREMENT NOT NULL, complementaryModality VARCHAR(2) NOT NULL, titleName VARCHAR(255) NOT NULL, institution VARCHAR(255) NOT NULL, hours INT NOT NULL, date DATE NOT NULL, certifiedPdf VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teaching_experience (id INT UNSIGNED AUTO_INCREMENT NOT NULL, isForeignUniversity TINYINT(1) DEFAULT NULL, snies CHAR(4) NOT NULL, nameUniversity VARCHAR(255) NOT NULL, faculty VARCHAR(255) NOT NULL, program VARCHAR(255) NOT NULL, dateAdmission DATE NOT NULL, isActive TINYINT(1) DEFAULT NULL, retirementDate DATE DEFAULT NULL, contractModality CHAR(2) NOT NULL, courseLoad LONGTEXT NOT NULL, certifiedPdf TEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE further_training');
        $this->addSql('DROP TABLE teaching_experience');
    }
}
