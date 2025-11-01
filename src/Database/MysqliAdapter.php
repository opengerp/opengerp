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
        if (!$st) {
            echo $this->mysqli->error;
            return false;
        }

        return new MysqliResult($st);
    }


    public function lastInsertId(): int
    {
        return $this->mysqli->insert_id;
    }

    public function escape_string(?string $string): string
    {

        $text = $text ?? '';

        return $this->mysqli->real_escape_string($text);


    }


}

