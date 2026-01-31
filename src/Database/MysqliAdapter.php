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
        } catch (\mysqli_sql_exception $e) {
            throw new \RuntimeException(
                sprintf(
                    "DB error (%d): %s\nSQL: %s",
                    $e->getCode(),
                    $e->getMessage(),
                    $sql
                ),
                0,
                $e
            );
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

