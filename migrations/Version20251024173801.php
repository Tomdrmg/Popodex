<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024173801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card ADD back_id INT NOT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3E9583FF0 FOREIGN KEY (back_id) REFERENCES card_back (id)');
        $this->addSql('CREATE INDEX IDX_161498D3E9583FF0 ON card (back_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3E9583FF0');
        $this->addSql('DROP INDEX IDX_161498D3E9583FF0 ON card');
        $this->addSql('ALTER TABLE card DROP back_id');
    }
}
