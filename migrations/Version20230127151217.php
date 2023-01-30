<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230127151217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE academic_training (id INT AUTO_INCREMENT NOT NULL, academic_modality VARCHAR(3) NOT NULL, date DATE NOT NULL, title_name VARCHAR(255) NOT NULL, snies VARCHAR(4) DEFAULT NULL, is_foreign_university TINYINT(1) NOT NULL, name_university VARCHAR(255) NOT NULL, degree_pdf LONGTEXT DEFAULT NULL, certified_title_pdf LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE further_training (id INT UNSIGNED AUTO_INCREMENT NOT NULL, complementaryModality VARCHAR(2) NOT NULL, titleName VARCHAR(255) NOT NULL, institution VARCHAR(255) NOT NULL, hours INT NOT NULL, date DATE NOT NULL, certifiedPdf VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teaching_experience (id INT UNSIGNED AUTO_INCREMENT NOT NULL, isForeignUniversity TINYINT(1) DEFAULT NULL, snies CHAR(4) NOT NULL, nameUniversity VARCHAR(255) NOT NULL, faculty VARCHAR(255) NOT NULL, program VARCHAR(255) NOT NULL, dateAdmission DATE NOT NULL, isActive TINYINT(1) DEFAULT NULL, retirementDate DATE DEFAULT NULL, contractModality VARCHAR(2) NOT NULL, courseLoad LONGTEXT NOT NULL, certifiedPdf TEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE work_experience (id INT AUTO_INCREMENT NOT NULL, companyName VARCHAR(255) NOT NULL, position VARCHAR(255) NOT NULL, dependence VARCHAR(255) NOT NULL, department VARCHAR(255) NOT NULL, municipality VARCHAR(5) NOT NULL, companyAddress VARCHAR(255) NOT NULL, bossName VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, admissionDate DATE NOT NULL, isWorking TINYINT(1) DEFAULT NULL, retirementDate DATE DEFAULT NULL, certifiedPdf TEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE academic_training');
        $this->addSql('DROP TABLE further_training');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE teaching_experience');
        $this->addSql('DROP TABLE work_experience');
    }
}
