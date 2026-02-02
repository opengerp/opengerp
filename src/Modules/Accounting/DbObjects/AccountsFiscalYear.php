<?php

namespace Opengerp\Modules\Accounting\DbObjects;

use Opengerp\Database\DbObject;
use Opengerp\Database\Column;

class AccountsFiscalYear extends DbObject
{

    const TABLE_NAME = 'COG_Esercizi';

    public const TABLE_PRIMARY_KEY = 'Num_Esercizio';


    public int $Num_Esercizio;                 // PK (smallint) - richiesto
    public string $Data_Inizio_Esercizio;      // date (Y-m-d)
    public ?string $Data_Fine_Esercizio = null; // date nullable
    public int $Stato_Esercizio = 1;           // smallint default 1
    public ?string $Json_Esercizio = null;     // text nullable
    public int $Utente;

    public function __construct()
    {
        $this->_columns['Data_Fine_Esercizio'] = new Column(Column::TYPE_DATE);
        $this->_columns['Data_Fine_Esercizio']->null = true;

    }

}
