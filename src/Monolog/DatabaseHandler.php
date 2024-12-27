<?php

declare(strict_types=1);

namespace App\Monolog;

use Doctrine\Persistence\ManagerRegistry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Component\Uid\Uuid;

class DatabaseHandler extends AbstractProcessingHandler
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
        parent::__construct();
    }

    protected function write(array|LogRecord $record): void
    {
        if ($record['level'] < 500) {
            return;
        }

        $sql = "
            INSERT INTO koi_error (id, message, level, level_name, trace, created_at) VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->managerRegistry->getConnection()->prepare($sql);
        $stmt->bindValue(1, Uuid::v7()->toRfc4122());
        $stmt->bindValue(2, $record['message']);
        $stmt->bindValue(3, $record['level']);
        $stmt->bindValue(4, $record['level_name']);
        $stmt->bindValue(5, json_encode($record['context']['exception']->getTrace()));
        $stmt->bindValue(6, new \DateTimeImmutable()->format('Y-m-d H:i:s'));
        $stmt->executeQuery();
    }
}
