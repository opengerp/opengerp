<?php

namespace Opengerp\Modules\Accounting\DbObjects;

use Opengerp\Database\DbObject;
use Opengerp\Database\Column;

class TaxCodes extends DbObject
{

    const TABLE_NAME = 'DOC_Iva';
    const TABLE_PRIMARY_KEY = 'Cod_Iva';

    public $Cod_Iva;
    public $Des_Iva;
    public $Perc_Iva;
    public $Perc_Indetraibile;
    public $CEE;


    public $Ab_Iva_Default;
    public $Json_Dati_Iva;
    public $Data_Obsoleto;




    public function __construct()
    {


        $this->_columns['Data_Obsoleto'] = new Column();
        $this->_columns['Data_Obsoleto']->null = true;


    }



}