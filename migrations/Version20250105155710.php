<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250105155710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25277294869C');
        $this->addSql('DROP INDEX IDX_F0FE25271AD5CDBF ON cart_item');
        $this->addSql('DROP INDEX IDX_F0FE25277294869C ON cart_item');
        $this->addSql('ALTER TABLE cart_item ADD offer_id INT NOT NULL, DROP cart_id, DROP article_id');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE252753C674EE FOREIGN KEY (offer_id) REFERENCES offre (id)');
        $this->addSql('CREATE INDEX IDX_F0FE252753C674EE ON cart_item (offer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE252753C674EE');
        $this->addSql('DROP INDEX IDX_F0FE252753C674EE ON cart_item');
        $this->addSql('ALTER TABLE cart_item ADD article_id INT NOT NULL, CHANGE offer_id cart_id INT NOT NULL');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25277294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F0FE25271AD5CDBF ON cart_item (cart_id)');
        $this->addSql('CREATE INDEX IDX_F0FE25277294869C ON cart_item (article_id)');
    }
}
