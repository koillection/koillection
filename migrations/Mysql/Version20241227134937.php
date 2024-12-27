<?php

declare(strict_types=1);

namespace App\Migrations\Mysql;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241227134937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Mysql] Add `koi_error`.';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof MySQLPlatform, 'Migration can only be executed safely on \'mysql\' or \'mariadb\'.');

        $this->addSql('CREATE TABLE koi_error (id CHAR(36) NOT NULL, message LONGTEXT NOT NULL, level SMALLINT NOT NULL, level_name VARCHAR(255) NOT NULL, trace JSON NOT NULL, count INT NOT NULL, last_occurrence_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(true, 'Always move forward.');
    }
}
