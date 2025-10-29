<?php

namespace Opengerp\Database;

interface DbAdapter {

    public function query(string $sql, array $params = []): DbResult;


    public function lastInsertId(): int;
}

