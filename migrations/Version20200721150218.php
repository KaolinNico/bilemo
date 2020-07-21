<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200721150218 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE phone (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(255) DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, processor VARCHAR(255) DEFAULT NULL, screen VARCHAR(255) DEFAULT NULL, camera VARCHAR(255) DEFAULT NULL, ram VARCHAR(255) DEFAULT NULL, network VARCHAR(255) DEFAULT NULL, connectivity VARCHAR(255) DEFAULT NULL, system VARCHAR(255) DEFAULT NULL, autonomy VARCHAR(255) DEFAULT NULL, dimensions VARCHAR(255) DEFAULT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE phone');
    }
}
