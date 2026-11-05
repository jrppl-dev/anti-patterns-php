<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261105170750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE todo_list ADD COLUMN settings CLOB DEFAULT \'[]\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__todo_list AS SELECT id, name, created_at, owner_id FROM todo_list');
        $this->addSql('DROP TABLE todo_list');
        $this->addSql('CREATE TABLE todo_list (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, owner_id INTEGER NOT NULL, CONSTRAINT FK_1B199E077E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO todo_list (id, name, created_at, owner_id) SELECT id, name, created_at, owner_id FROM __temp__todo_list');
        $this->addSql('DROP TABLE __temp__todo_list');
        $this->addSql('CREATE INDEX IDX_1B199E077E3C61F9 ON todo_list (owner_id)');
    }
}
