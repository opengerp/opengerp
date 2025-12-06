<?php

namespace Opengerp\Core\DbObjects;

use Opengerp\Database\DbObject;

use Opengerp\Database\Column;

class Moduli extends DbObject
{

    const TABLE_NAME = 'INT_Moduli';
    const TABLE_PRIMARY_KEY = 'ID';

    const TABLE_ENTITY = 'Modules';

    public $ID;
    public $Descrizione;
    public $Descrizione_Ext;
    public $Voce_Menu;
    public $ID_Parent;
    public $Controller;
    public $Get_Vars;
    public $Custom;

    public $Json_Config;
    public $Json_Parametri_Utenti;


    public function __construct()
    {
        $this->_columns['ID'] = new Column();
        $this->_columns['ID']->primary_key = true;


        $this->_columns['Custom'] = new Column();
        $this->_columns['Custom']->type = Column::TYPE_INT;
        $this->_columns['Custom']->default = 0;

    }




}
