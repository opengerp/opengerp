<?php

namespace Opengerp\Database;

final class PdoAdapter implements DbAdapter
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }


    public function query(string $sql, array $params = []): DbResult
    {
        $st = $this->pdo->query($sql, MYSQLI_ASSOC);

        return new PdoResult($st);
    }

    public function lastInsertId(): int
    {

        $id = $this->pdo->lastInsertId();
        return ctype_digit($id)?(int)$id:$id;

    }

    public function escape_string(string $string): string
    {

        $text = $text ?? '';

        $text = $this->pdo->quote($text);

        return mb_substr($text, 1, mb_strlen($text) - 2);



    }

}