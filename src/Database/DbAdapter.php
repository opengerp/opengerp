<?php

namespace Opengerp\Database;

interface DbAdapter
{

    public function query(string $sql, array $params = []): DbResult;


    public function lastInsertId(): int;

    public function escape_string(string $string) : string;

}

