<?php


namespace Opengerp\Core\DbObjects;

use Opengerp\Database\DbObject;
use Opengerp\Database\Column;

class UserAuth extends DbObject
{

    const TABLE_ENTITY = 'Users';
    const TABLE_NAME = 'INT_Utenti_Aut';
    const TABLE_PRIMARY_KEY = 'Username';

    public ?string $Username = '';

    public ?int $ID_Utente = null;

    public ?string $Password = '';
    public ?string $Lastlogin = null;
    public ?string $IP = '';
    public int $Ab_Internet = 0;
    public int $Ab_Vis_Costi = 0;
    public ?string $Ultimo_IP = '';
    public int $Tipo_Menu = 1;
    public ?string $Data_Cambio_Password = null;
    public string $Cod_Lingua = 'IT';
    public ?string $Parametri_Email = null;
    public ?string $Parametri_Aut = null;
    public ?string $Token_Api = null;
    public ?string $Authenticator_Secret = null;


    public function __construct()
    {
        $this->_columns['Username'] = new Column(Column::TYPE_TEXT);
        $this->_columns['Username']->primary_key = true;

        $this->_columns['ID_Utente'] = new Column(Column::TYPE_INT);
        $this->_columns['ID_Utente']->null = false;


        $this->_columns['Lastlogin'] = new Column(Column::TYPE_DATETIME);
        $this->_columns['Lastlogin']->null = true;


        $this->_columns['Data_Cambio_Password'] = new Column(Column::TYPE_DATETIME);
        $this->_columns['Data_Cambio_Password']->null = true;




    }

}
