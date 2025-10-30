<?php


namespace Opengerp\UserInterface\Tables;


interface TableInterface
{

    public function addColumn(string $key, ?string $des) : TableColumn;


}