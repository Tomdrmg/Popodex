<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024173430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, series_id INT NOT NULL, name VARCHAR(255) NOT NULL, date DATE NOT NULL, image VARCHAR(255) NOT NULL, background_image VARCHAR(255) DEFAULT NULL, full_art TINYINT(1) NOT NULL, image_vertical_position INT NOT NULL, border_opacity INT NOT NULL, border_width INT NOT NULL, center_moves TINYINT(1) NOT NULL, moves_margin_top INT NOT NULL, INDEX IDX_161498D35278319C (series_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_back (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, background_image VARCHAR(255) NOT NULL, border_opacity INT NOT NULL, border_width INT NOT NULL, font_size INT NOT NULL, outline_width INT NOT NULL, text_position INT NOT NULL, curvature INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_move (id INT AUTO_INCREMENT NOT NULL, card_id INT NOT NULL, title VARCHAR(255) NOT NULL, details VARCHAR(2000) NOT NULL, INDEX IDX_30D35DA4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE series (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D35278319C FOREIGN KEY (series_id) REFERENCES series (id)');
        $this->addSql('ALTER TABLE card_move ADD CONSTRAINT FK_30D35DA4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D35278319C');
        $this->addSql('ALTER TABLE card_move DROP FOREIGN KEY FK_30D35DA4ACC9A20');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_back');
        $this->addSql('DROP TABLE card_move');
        $this->addSql('DROP TABLE series');
    }
}
