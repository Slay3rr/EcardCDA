<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211134435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL');
    }

/*************  ✨ Codeium Command ⭐  *************/
    /**
     * Reverts the changes made in the up() method by removing the 'created_at' column
     * from the 'user' table.
     *
     * @param Schema $schema The schema to be modified.
     */

/******  a8d4848c-9f14-4133-8f19-e8996a326a21  *******/
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP created_at');
    }
}
