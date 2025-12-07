<?php

namespace Opengerp\Core\DbObjects;

use Opengerp\Database\DbObject;

class MenuItems extends DbObject
{


    public const TABLE_NAME = 'INT_Menu';
    public const TABLE_PRIMARY_KEY = 'ID_Menu';

    public $ID_Menu;
    public $Voce_Menu;
    public $Icon;

    public $Json_Traduzione;

}
