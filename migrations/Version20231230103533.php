<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231230103533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentary DROP FOREIGN KEY FK_1CAC12CAB03A8386');
        $this->addSql('DROP INDEX IDX_1CAC12CAB03A8386 ON commentary');
        $this->addSql('ALTER TABLE commentary DROP created_by_id');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EB03A8386');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EE9414162');
        $this->addSql('DROP INDEX IDX_D8F0A91EE9414162 ON trick');
        $this->addSql('DROP INDEX IDX_D8F0A91EB03A8386 ON trick');
        $this->addSql('ALTER TABLE trick ADD tricks_group_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, DROP trickgroup_id, DROP created_by_id, CHANGE mainimage image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EDE4E02E0 FOREIGN KEY (tricks_group_id) REFERENCES tricks_group (id)');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91EDE4E02E0 ON trick (tricks_group_id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91EA76ED395 ON trick (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentary ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CAB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1CAC12CAB03A8386 ON commentary (created_by_id)');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EDE4E02E0');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EA76ED395');
        $this->addSql('DROP INDEX IDX_D8F0A91EDE4E02E0 ON trick');
        $this->addSql('DROP INDEX IDX_D8F0A91EA76ED395 ON trick');
        $this->addSql('ALTER TABLE trick ADD trickgroup_id INT DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, DROP tricks_group_id, DROP user_id, CHANGE image_name mainimage VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EE9414162 FOREIGN KEY (trickgroup_id) REFERENCES tricks_group (id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91EE9414162 ON trick (trickgroup_id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91EB03A8386 ON trick (created_by_id)');
    }
}
