<?php

namespace Opengerp\Database;


final class MysqliResult implements DbResult
{
    private \mysqli_result $result;

    public function __construct(\mysqli_result $result)
    {
        $this->result = $result;

    }

    public function fetch(): ?array
    {

        if ($this->result === null) {
            return null;
        }

        return $this->result->fetch_assoc();


    }

    public function num_rows(): int
    {
        if ($this->result === null) {
            return 0;
        }

        return $this->result->num_rows;
    }

    public function affected_rows(): int
    {
        return 0;
    }

    public function fetchAll(): ?array
    {
        return $this->result->fetch_all(MYSQLI_ASSOC);
    }

}