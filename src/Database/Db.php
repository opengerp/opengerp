<?php

namespace Opengerp\Database;

class Db
{
    private DbAdapter $adapter;

    public function __construct(DbAdapter $adapter)
    {
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

    public function escape_string(?string $value): string
    {
        return $this->adapter->escape_string($value);
    }

    public function sql_found_rows()
    {
        $ris = $this->query('SELECT FOUND_ROWS() ');

        if ($lin = $ris->fetch()) {


            return $lin['FOUND_ROWS()'];

        }

        return 0;

    }


    public function sql_cast_date($nomecampo)
    {

        return " CAST($nomecampo AS DATE) ";

    }



    public function sql_datepart($parte, $campo_data)
    {

        if ($parte=="yy") {

            $parte  = "YEAR";

        } else if ($parte == "mm") {

            $parte  = "MONTH";

        }

        $str = "EXTRACT($parte FROM $campo_data)";


        return $str;


    }





}

