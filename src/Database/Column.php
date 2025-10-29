<?php

namespace Opengerp\Database;

class Column
{

    const TYPE_INT = 'int';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_TEXT = 'text';
    const TYPE_JSON = 'json';
    const TYPE_ENUM = 'enum';


    const DEFAULT_NOW = 'now';

    public $value;

    public $name;
    public $type;

    public $primary_key = array();

    public $autoincrement;

    public $decoded_json;


    public $default;

    public $null;

    public function __construct($type = null)
    {
        if ($type != null) {
            $this->type = $type;

        }
    }
}
