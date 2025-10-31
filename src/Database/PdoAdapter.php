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


        try {

            $st = $this->pdo->query($sql, MYSQLI_ASSOC);

        } catch (\PDOException $e) {
            // Info “grezze”
            error_log('PDO ERROR: '.$e->getMessage());
            throw $e; // o rethrow con messaggio arricchito
        }



        return new PdoResult($st);
    }

    public function lastInsertId(): int
    {

        $id = $this->pdo->lastInsertId();
        return ctype_digit($id)?(int)$id:$id;

    }

    public function escape_string(?string $string): string
    {

        $string = $string ?? '';

        $string = $this->pdo->quote($string);

        return mb_substr($string, 1, mb_strlen($string) - 2);



    }

}