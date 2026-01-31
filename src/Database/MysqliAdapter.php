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
        try {
            $st = $this->mysqli->query($sql);

        } catch (\Exception $e) {

            throw new \Exception($sql);
        }

        if ( ! ($st instanceof \mysqli_result) ) {

            $st = null;

        }

        return new MysqliResult($st);
    }


    public function lastInsertId(): int
    {
        return $this->mysqli->insert_id;
    }

    public function escape_string(?string $string): string
    {

        $string = $string ?? '';

        return $this->mysqli->real_escape_string($string);


    }


}

