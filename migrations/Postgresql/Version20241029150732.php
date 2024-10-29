<?php

declare(strict_types=1);

namespace App\Migrations\Postgresql;

use App\Enum\DatumTypeEnum;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241029150732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Postgresql] Add `currency` to `koi_datum`.';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Migration can only be executed safely on \'postgresql\'.');

        $type = DatumTypeEnum::TYPE_PRICE;

        $this->addSql('ALTER TABLE koi_datum ADD currency VARCHAR(3) DEFAULT NULL');
        $this->addSql("UPDATE koi_datum SET currency = (SELECT owner.currency FROM koi_user owner WHERE owner.id = owner_id) WHERE type = '{$type}' AND currency IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(true, 'Always move forward.');
    }
}
