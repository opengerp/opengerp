<?php

namespace Opengerp\UserInterface\Tables;

class TableColumn
{
    public string $key;
    public string $des;

    public string $type = 'text';

    public ?string $formatter = null;

    public bool $escape = false;


}