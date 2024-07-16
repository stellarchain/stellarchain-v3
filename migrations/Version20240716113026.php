<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716113026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ALTER content TYPE TEXT');
        $this->addSql('ALTER TABLE comment ALTER content DROP NOT NULL');
        $this->addSql('ALTER TABLE comment ALTER content TYPE TEXT');
        $this->addSql('ALTER TABLE community ADD description VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD content TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ALTER description TYPE VARCHAR(1000)');
        $this->addSql('ALTER TABLE project ALTER description DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP content');
        $this->addSql('ALTER TABLE project ALTER description TYPE TEXT');
        $this->addSql('ALTER TABLE project ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE project ALTER description TYPE TEXT');
        $this->addSql('ALTER TABLE comment ALTER content TYPE VARCHAR(500)');
        $this->addSql('ALTER TABLE comment ALTER content SET NOT NULL');
        $this->addSql('ALTER TABLE community DROP description');
    }
}
