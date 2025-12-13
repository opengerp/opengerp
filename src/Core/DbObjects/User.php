<?php

namespace Opengerp\Core\DbObjects;

use Opengerp\Database\DbObject;
use Opengerp\Database\Column;


class User extends DbObject
{

    const TABLE_ENTITY = 'Users';
    const TABLE_NAME = 'INT_Utenti';
    const TABLE_PRIMARY_KEY = 'ID';

    public $ID;
    public $Nome;
    public $Cognome;
    public $Titolo;
    public $Des_Azienda;
    public $Azienda;
    public $ID_Cliente_Utente;
    public $Data_Nascita;
    public $Cod_Localita_Nas;
    public $Cod_Nazione_Nas;
    public $Des_Localita_Nas;
    public $Cod_Cap;
    public $Sig_Prov;
    public $Indirizzo;
    public $Indirizzo2;

    public $Des_Localita;
    public $Codice_Fiscale;
    public $Cod_Localita;
    public $Cod_Nazione;
    public $Fisso;
    public $Mobile;
    public $Interno;
    public $Email;
    public $Email_Intranet;
    public $Note_Utente;
    public $ID_Role;
    public $ID_Reparto;
    public $Stato_Utente;
    public $Utente;
    public $Str_Activation;
    public $Date_Inserted;
    public $Data_Ultimo_Agg;
    public $Json_Utente;


    public function __construct()
    {
        $this->_columns['ID'] = new Column(Column::TYPE_INT);
        $this->_columns['ID']->primary_key = true;
        $this->_columns['ID']->null = false;
        $this->_columns['ID']->autoincrement = true;

        $this->_columns['Nome'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Nome']->null = true;

        $this->_columns['Cognome'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Cognome']->null = true;

        $this->_columns['Titolo'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Titolo']->null = true;

        $this->_columns['Des_Azienda'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Des_Azienda']->null = true;

        $this->_columns['Azienda'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Azienda']->null = true;
        $this->_columns['Azienda']->default = NULL;

        $this->_columns['ID_Cliente_Utente'] = new Column(Column::TYPE_INT);
        $this->_columns['ID_Cliente_Utente']->null = true;

        $this->_columns['Data_Nascita'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Data_Nascita']->null = true;

        $this->_columns['Cod_Localita_Nas'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Cod_Localita_Nas']->null = true;

        $this->_columns['Cod_Nazione_Nas'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Cod_Nazione_Nas']->null = true;

        $this->_columns['Des_Localita_Nas'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Des_Localita_Nas']->null = true;

        $this->_columns['Cod_Cap'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Cod_Cap']->null = true;

        $this->_columns['Sig_Prov'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Sig_Prov']->null = true;

        $this->_columns['Indirizzo'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Indirizzo']->null = true;

        $this->_columns['Des_Localita'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Des_Localita']->null = true;

        $this->_columns['Codice_Fiscale'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Codice_Fiscale']->null = true;

        $this->_columns['Cod_Localita'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Cod_Localita']->null = true;

        $this->_columns['Cod_Nazione'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Cod_Nazione']->null = true;

        $this->_columns['Fisso'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Fisso']->null = true;

        $this->_columns['Fisso'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Fisso']->null = true;

        $this->_columns['Interno'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Interno']->null = true;

        $this->_columns['Email'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Email']->null = true;

        $this->_columns['Email_Intranet'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Email_Intranet']->null = true;

        $this->_columns['Note_Utente'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Note_Utente']->null = true;

        $this->_columns['ID_Role'] = new Column(Column::TYPE_INT);
        $this->_columns['ID_Role']->null = false;
        $this->_columns['ID_Role']->default = '0';

        $this->_columns['ID_Reparto'] = new Column(Column::TYPE_INT);
        $this->_columns['ID_Reparto']->null = true;

        $this->_columns['Stato_Utente'] = new Column(Column::TYPE_INT);
        $this->_columns['Stato_Utente']->null = true;

        $this->_columns['Utente'] = new Column(Column::TYPE_INT);
        $this->_columns['Utente']->null = true;

        $this->_columns['Str_Activation'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Str_Activation']->null = true;

        $this->_columns['Date_Inserted'] = new Column(Column::TYPE_DATETIME);
        $this->_columns['Date_Inserted']->null = true;

        $this->_columns['Data_Ultimo_Agg'] = new Column(Column::TYPE_DATETIME);
        $this->_columns['Data_Ultimo_Agg']->null = true;

        $this->_columns['Json_Utente'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Json_Utente']->null = true;

    }

}
