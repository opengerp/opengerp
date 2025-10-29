<?php

namespace Opengerp\Database;


final class MysqliAdapter implements DbAdapter
{
    private \mysqli $mysqli;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        $this->mysqli->set_charset('utf8mb4');
    }


    public function query(string $sql, array $params = []): DbResult
    {
        $st = $this->mysqli->query($sql);

        return new MysqliResult($st, $res);
    }


    public function lastInsertId(): int
    {
        return $this->mysqli->insert_id;
    }


}

