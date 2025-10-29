<?php

namespace Opengerp\Database;

final class Db
{
    private DbAdapter $adapter;

    public function __construct(DbAdapter $adapter) {
        $this->adapter = $adapter;
    }

    public function query(string $sql, array $params = []): DbResult
    {
        return $this->adapter->query($sql, $params);
    }

    public function lastInsertId(): int
    {
        return $this->adapter->lastInsertId();
    }
}

