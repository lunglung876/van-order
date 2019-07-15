<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190712072802 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` ADD origin_latitude VARCHAR(20) NOT NULL, ADD origin_longitude VARCHAR(20) NOT NULL, ADD destination_latitude VARCHAR(20) NOT NULL, ADD destination_longitude VARCHAR(20) NOT NULL, DROP origin, DROP destination, CHANGE distance distance INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` ADD origin LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, ADD destination LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, DROP origin_latitude, DROP origin_longitude, DROP destination_latitude, DROP destination_longitude, CHANGE distance distance DOUBLE PRECISION DEFAULT NULL');
    }
}
