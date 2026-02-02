<?php

namespace Opengerp\Modules\Accounting\DbObjects;

use Opengerp\Database\DbObject;
use Opengerp\Database\Column;

class PaymentTerms extends DbObject
{


    const TABLE_NAME = 'DOC_Pagamenti';
    const TABLE_PRIMARY_KEY = 'Cod_Pagamento';


    public $Cod_Pagamento;
    public $Cod_Tipo_Pagamento;
    public $Perc_Sconto_Pagamento;
    public $Des_Pagamento;

    public $Num_Giorni;
    public $Ab_Controllo_Saldo;

    public $Giorno_Fisso;
    public $Val_Spese_Pag;
    public $Ab_Fine_Mese;
    public $Ab_Chiusura_Automatica;

    public $Str_Slittamento;
    public $Default_Pagamento;
    public $Str_Parametri;
    public $Data_Obsoleto;



    public function __construct()
    {

        $this->_columns['Ab_Fine_Mese'] = new Column();
        $this->_columns['Ab_Fine_Mese']->type = Column::TYPE_INT;

        $this->_columns['Ab_Controllo_Saldo'] = new Column();
        $this->_columns['Ab_Controllo_Saldo']->type = Column::TYPE_INT;

        $this->_columns['Giorno_Fisso'] = new Column();
        $this->_columns['Giorno_Fisso']->type = Column::TYPE_INT;

        $this->_columns['Data_Obsoleto'] = new Column();
        $this->_columns['Data_Obsoleto']->null = true;
        $this->_columns['Data_Obsoleto']->type = Column::TYPE_DATE;

        $this->_columns['Val_Spese_Pag'] = new Column();
        $this->_columns['Val_Spese_Pag']->type = Column::TYPE_DECIMAL;

        $this->_columns['Str_Parametri'] = new Column();
        $this->_columns['Str_Parametri']->type = Column::TYPE_TEXT;

        $this->_columns['Default_Pagamento'] = new Column();
        $this->_columns['Default_Pagamento']->type = Column::TYPE_INT;
        $this->_columns['Default_Pagamento']->null = false;

        $this->_columns['Str_Slittamento'] = new Column();
        $this->_columns['Str_Slittamento']->type = Column::TYPE_TEXT;

        $this->_columns['Num_Giorni'] = new Column();
        $this->_columns['Num_Giorni']->type = Column::TYPE_INT;

        $this->_columns['Des_Pagamento'] = new Column();
        $this->_columns['Des_Pagamento']->type = Column::TYPE_TEXT;

        $this->_columns['Perc_Sconto_Pagamento'] = new Column();
        $this->_columns['Perc_Sconto_Pagamento']->type = Column::TYPE_DECIMAL;
        $this->_columns['Perc_Sconto_Pagamento']->null = false;

        $this->_columns['Cod_Tipo_Pagamento'] = new Column();
        $this->_columns['Cod_Tipo_Pagamento']->type = Column::TYPE_TEXT;

        $this->_columns['Cod_Pagamento'] = new Column();
        $this->_columns['Cod_Pagamento']->null = false;
        $this->_columns['Cod_Pagamento']->type = Column::TYPE_TEXT;
        $this->_columns['Cod_Pagamento']->primary_key = true;

        $this->_columns['Ab_Chiusura_Automatica'] = new Column();
        $this->_columns['Ab_Chiusura_Automatica']->type = Column::TYPE_INT;
        $this->_columns['Cod_Pagamento']->null = true;
    }




}
