<?php

namespace Opengerp\UserInterface\Tables;

class Table implements TableInterface
{
    public $empty_text = '&nbsp;';
    public $columns = [];

    public $rows = [];

    public function addColumn(string $key, ?string $des, $type = 'string'): TableColumn
    {


        $col = new TableColumn();
        $col->key = $key;
        $col->des = $des;
        $col->type = $type;

        $this->columns[] = $col;


        return $col;


    }

    public function caption()
    {
        return null;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function emptyText()
    {
        return $this->empty_text;
    }

    public function setRows($rows)
    {


        $this->rows = $rows;
    }


}