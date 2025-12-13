<?php

namespace Opengerp\Modules\Users\Service;

use Opengerp\App\Config;
use \Opengerp\Modules\Users\Domain\Roles;

class Utente
{

    public $id;
    public $vett_dati = array();
    public $vett_auth = array();

    // usata nelle iscrizioni + invio mail
    public string $plain_password = '';

    const STATO_ELIMINATO = 99;


    public function ripristina()
    {

        global $obj_utente;
        $user = $obj_utente->getUserId();

        $utente = $this->id;

        if (!$utente) {
            return false;
        }

        gsql_query("UPDATE INT_Utenti SET Stato_Utente=1,
        Utente='" . $user . "', Data_Ultimo_Agg=" . SQL_GETDATE . "
        WHERE ID=" . $utente . ";");
        return true;


    }


    public function inviaEmail($cat_mail = null)
    {

        $gerp_site_cfg = \Gerp_Config::getSiteConfig();

        // controlla email

        // controlla configurazione
        if (!Gerp_Config::get('amm_ute', 'GERP_SMTP')) {
            gerp_display_error("SMTP non configurato");
            return false;
        }

        if (!$this->id) {
            gerp_display_error("Utente non configurato correttamente");
            return false;

        }

        $this->carica();
        $this->caricaAuth();

        if (!$this->vett_dati['Email']) {
            gerp_display_error("Email utente non configurata.");
            return false;
        }

        if ($gerp_site_cfg) {
            $url = $gerp_site_cfg['path'];
        } else {
            $url = URL_SISTEMA_LOCALE;
        }

        $tpl = new Gerp_Template();
        $tpl->assign('auth', $this->vett_auth);
        $tpl->assign('utente', $this->vett_dati);

        if ($this->plain_password != '') {
            $tpl->assign('password', $this->plain_password);
        } else {
            $tpl->assign('password', $this->vett_auth['Password']);
        }

        $tpl->assegnaGlobali();

        $tpl->assign('url', $url);
        $tpl->assign('str_activation', $this->vett_dati['Str_Activation']);
        $tpl->assign('id', $this->id);


        global $include_prefix;


        if (file_exists($include_prefix . 'custom/templates/email_registrazione.tpl')) {
            $testo = $tpl->fetch($include_prefix . 'custom/templates/email_registrazione.tpl');


        } else {
            $testo = $tpl->fetch($include_prefix . 'templates/templates/documenti/email_registrazione.tpl');
        }

        if ($cat_mail == 'requestPassword') {

            $subject = trans('Richiesta cambio password');

        } else {
            $subject = trans('Registrazione');

        }

        if (Gerp_Mailer::invia_mail($this->vett_dati['Email'], $subject, $testo)) {


            return true;

        }


    }

    public function eliminaAuth($utente)
    {

        if (!$utente) {
            return false;

        }
        gsql_query("DELETE FROM INT_Utenti_Moduli WHERE ID_Utente= '$utente' ");
        gsql_query("DELETE FROM INT_Utenti_Aut WHERE ID_Utente = '$utente' ");


        return true;

    }


    public function salvaAuth($utente, $vett, $change_pwd = true)
    {
        $cod_lingua = '';


        $vett_parametri = array(
            'ab_internet' => 0,
            'str_username' => '',
            'str_password' => '',
            'ab_vis_costi' => 0,
            'cod_lingua' => 'IT');

        // TODO: assegnare al vettore e poi usare extract per evitare variabile di variabile ?
        foreach ($vett_parametri as $k => $v) {
            if (isset($vett[$k])) {
                // valore dall'esterno
                $$k = trim($vett[$k]);
            } else {
                // valore default
                $$k = $v;
            }
        }

        $this->plain_password = $str_password;
        $str_password = password_hash($str_password, PASSWORD_DEFAULT);


        if (!$utente) {
            \Opengerp\Core\Console\Console::appendError('Utente non selezionato');
            return false;
        }

        if (strlen($str_username) < 2) {
            \Opengerp\Core\Console\Console::appendError('Username non valida, deve essere almeno di 2 caratteri');
            return false;
        }

        if (!\Opengerp\Utils\Strings\Filters::isValidUsername($str_username)) {
            \Opengerp\Core\Console\Console::appendError('Username non valida, non pu&ograve; contenere spazi.');
            return false;
        }


        $ris_check = gsql_query("SELECT  INT_Utenti_Aut.* FROM INT_Utenti_Aut
             WHERE Username = '$str_username'
                AND ID_Utente != '$utente'");

        if ($lin_check = gsql_fetch_assoc($ris_check)) {
            \Opengerp\Core\Console\Console::appendError("Username gi&agrave; in uso");
            return false;
        }


        // controlla se esiste auth
        $ris_m = gsql_query("SELECT  INT_Utenti_Aut.*
        FROM INT_Utenti_Aut  WHERE ID_Utente='$utente'");


        if (!$lin_m = gsql_fetch_assoc($ris_m)) {

            if (\Opengerp\App\Config::get('amm_ute', 'max_active_users') > 0) {

                $ris_controllo = gsql_query("SELECT COUNT(ID_Utente) AS Numero FROM INT_Utenti_Aut ");

                if ($lin_controllo = gsql_fetch_assoc($ris_controllo)) {
                    if ($lin_controllo['Numero'] >= Config::get('amm_ute', 'max_active_users')) {
                        \Opengerp\Core\Console\Console::appendError('Superati numero di utenti attivi concessi: %d', [Gerp_Config::get('amm_ute', 'max_active_users')]);
                        return false;
                    }
                }

            }


            $dbo = new \Opengerp\Core\DbObjects\UserAuth();
            $dbo->ID_Utente = $utente;
            $dbo->Username = $str_username;
            $dbo->Password = $str_password;
            $dbo->Cod_Lingua = $cod_lingua;

            $dbo->Ab_Internet = $ab_internet;


            $dbo->insert();


        }


        $dbo = new \Opengerp\Core\DbObjects\UserAuth();
        $dbo->loadById($str_username);
        $dbo->Username = $str_username;
        $dbo->Cod_Lingua = $cod_lingua;
        $dbo->Ab_Internet = $ab_internet;
        $dbo->Ab_Vis_Costi = $ab_vis_costi;

        if ($change_pwd) {
            $dbo->Password = $str_password;
        }

        $dbo->update();


        //recovery of user parameters e.g. column customization
        $vett_user_params = [];
        $csv_moduli = \Opengerp\Utils\Arrays\Helper::toStringCsv($vett['vett_moduli']);
        if ($csv_moduli != '') {
            $ris = gsql_query(
                "SELECT ID_Modulo, Json_User_Params FROM INT_Utenti_Moduli WHERE ID_Utente='$utente' AND ID_Modulo IN ($csv_moduli) "
            );
            while ($lin = gsql_fetch_assoc($ris)) {
                $vett_user_params[$lin['ID_Modulo']] = $lin['Json_User_Params'];
            }
        }


        //costruzione della query di eliminazione dei moduli
        //elimina tutti i moduli assegnati all'utente
        $str_sqldel = "DELETE FROM INT_Utenti_Moduli WHERE ID_Utente='$utente' ";
        gsql_query($str_sqldel);
        //costruzione della query di inserimento dei moduli checkati
        //inserisce i nuovi moduli assegnati all'utente


        if (!isset($vett['vett_moduli'])) {
            return true;
        }

        if (!is_array($vett['vett_moduli'])) {
            return true;
        }

        $parametri = $vett['parametri'];

        if (is_array($parametri['produz']['id_risorsa'])) {

            $parametri['produz']['id_risorsa'] = implode(',', $parametri['produz']['id_risorsa']);

        }

        foreach ($vett['vett_moduli'] as $i => $id_modulo) {

            if (!isset($parametri[$id_modulo])) {
                $parametri[$id_modulo] = array();
            }

            if (!is_array($parametri[$id_modulo])) {
                $str_parametri = ($parametri[$id_modulo]);

            } else {
                $str_parametri = http_build_query($parametri[$id_modulo]);

            }

            $this->salvaModulo($utente, $id_modulo, $str_parametri);

            if (isset($vett_user_params[$id_modulo])) {

                $json = $vett_user_params[$id_modulo];
                gsql_query(" UPDATE INT_Utenti_Moduli SET Json_User_Params = '$json' WHERE INT_Utenti_Moduli.ID_Utente = '$utente' AND ID_Modulo = '$id_modulo' ");
            }

        }

        return true;

    }


    public function salvaModulo($utente, $id_modulo, $str_parametri = '')
    {

        $ris = gsql_query("SELECT * FROM INT_Utenti_Moduli WHERE ID_Utente = '$utente' AND ID_Modulo = '$id_modulo' ");

        if ($lin = gsql_fetch_assoc($ris)) {

            return false;
        }

        gsql_query("INSERT INTO INT_Utenti_Moduli(ID_Utente, ID_Modulo, Parametri)
             VALUES ('$utente','$id_modulo','$str_parametri') ");


    }


    public function visualizzaDati()
    {
        $utente = $this->id;

        if ($utente) {
            $this->carica();
            $lin_m = correggi_input_html($this->vett_dati);
        } else {
            $lin_m = array('Stato_Utente' => 1,
                'Cod_Localita' => false,
                'Cod_Localita_Nas' => false,
                'Data_Nascita' => false,
                'Cod_Nazione' => 'IT',
                'ID_Cliente_Utente' => false);

        }

        $utente_tpl = new Gerp_Template();
        $utente_tpl->assegnaGlobali();

        //controllo se esiste un codice agente assegnato. Se non esiste, assegna comando per inserimento
        $ris = gsql_query("SELECT Codice_Agente FROM PRE_Agenti WHERE ID_Utente = '$utente' ");

        if ($lin = gsql_fetch_array($ris)) {
            $utente_tpl->assign("cod_agente_exist", true);
        }

        //imposta il comando di inserimento in caso di nuovo utente

        if ($lin_m['Stato_Utente'] == Gerp_Utente::STATO_ELIMINATO) {
            $utente_tpl->assign("archiviato", true);
        }

        // vecchio database
        if (isset($lin_m['cod_localita'])) {
            $lin_m['Cod_Localita'] = $lin_m['cod_localita'];
        }

        // residenza
        if ($lin_m['Cod_Localita']) {

            $ris_loc = gsql_query("SELECT * FROM INT_Localita WHERE Cod_Localita='" . $lin_m["Cod_Localita"] . "'");

            if ($lin_loc = correggi_input_html(gsql_fetch_array($ris_loc))) {

                if (!$lin_m['Cod_Cap']) {
                    $lin_m["Cap"] = $lin_loc["Cod_Cap"];
                }

                if (!$lin_m['Sig_Prov']) {
                    $lin_m['Sig_Prov'] = $lin_loc["Sig_Prov"];
                }

                $lin_m["Citta"] = $lin_loc["Des_Localita"];
            }
        }

        // nascita
        if ($lin_m['Cod_Localita_Nas']) {

            $ris_loc = gsql_query("SELECT * FROM INT_Localita WHERE Cod_Localita='" . $lin_m['Cod_Localita_Nas'] . "'");
            if ($lin_loc = gsql_fetch_array($ris_loc)) {
                $lin_m['Des_Localita_Nas'] = $lin_loc['Des_Localita'];
                $lin_m['Provincia_Nas'] = $lin_loc['Sig_Prov'];
            }

        }

        $lin_m['Data_Nascita'] = rtrim($lin_m['Data_Nascita']);


        if (!$lin_m['Cod_Nazione']) {
            $lin_m['Cod_Nazione'] = COD_NAZIONE_ITALIA;
        }

        $utente_tpl->assegnaGlobali();


        /*
        if (isset($lin_m['ID_Role'])) {
            if ($lin_m['ID_Role'] > 0 && $lin_m['ID_Role']!=7 && $lin_m['ID_Role']!=8) {
                $lin_m['ID_Role'] = 1;
            }
        }*/


        $utente_tpl->assign("utente", $lin_m);
        //selezione tutti i ruoli in modo da visualizzarli nel template

        $vett_ruoli = Roles::caricaRuoli();

        foreach ($vett_ruoli as $k => $v) {
            $vett_temp = array('Progressivo' => $k, 'Descrizione' => $v);
            $utente_tpl->append('ruoli', $vett_temp);
        }


        $utente_tpl->assign('reparti', \Gerp\Utenti\RepartiRepository::fetchAll());

        $obj_azienda = new Gerp_Azienda();

        $obj_azienda->caricaNazioni($utente_tpl, true);

        if ($lin_m['ID_Cliente_Utente']) {
            $obj_cliente = new Cliente($lin_m['ID_Cliente_Utente']);
            $obj_cliente->carica();
            $utente_tpl->assign("ragione_sociale", correggi_input_html($obj_cliente->ragione_sociale));

            $utente_tpl->assign('cliente', $obj_cliente->vett_dati);
        }

        $vett_stored = (array)json_decode($this->vett_dati['Json_Utente'], true);

        $vett_param = explode(',', Gerp_Config::get('amm_ute', 'custom_fields'));

        $utente_tpl->assign('altri_dati', Gerp\Core\AttributeHelper::fetchMergeAttributes($vett_stored, $vett_param));

        $utente_tpl->display("utenti/ute_visualizza.tpl");


    } // end modifica


    public function modifica()
    {
        $utente = $this->id;

        if ($utente) {
            $this->carica();
            $lin_m = correggi_input_html($this->vett_dati);
        } else {
            $lin_m = array('Stato_Utente' => 1,
                'Cod_Localita' => false,
                'Cod_Localita_Nas' => false,
                'Data_Nascita' => false,
                'Cod_Nazione' => 'IT',
                'ID_Cliente_Utente' => false);
        }

        $utente_tpl = new Gerp_Template();
        $utente_tpl->assegnaGlobali();

        //imposta il comando di inserimento in caso di nuovo utente

        if ($lin_m['Stato_Utente'] == 99) {
            $utente_tpl->assign("archiviato", true);
        }


        // vecchio database
        if (isset($lin_m['cod_localita'])) {
            $lin_m['Cod_Localita'] = $lin_m['cod_localita'];
        }

        // residenza
        if ($lin_m['Cod_Localita']) {

            $ris_loc = gsql_query("SELECT * FROM INT_Localita WHERE Cod_Localita='" . $lin_m["Cod_Localita"] . "'");

            if ($lin_loc = correggi_input_html(gsql_fetch_array($ris_loc))) {

                if (!$lin_m['Cod_Cap']) {
                    $lin_m["Cap"] = $lin_loc["Cod_Cap"];
                }

                if (!$lin_m['Sig_Prov']) {
                    $lin_m['Sig_Prov'] = $lin_loc["Sig_Prov"];
                }

                $lin_m["Citta"] = $lin_loc["Des_Localita"];
            }

        }


        // nascita
        if ($lin_m['Cod_Localita_Nas']) {
            $ris_loc = gsql_query("SELECT * FROM INT_Localita WHERE Cod_Localita='" . $lin_m['Cod_Localita_Nas'] . "'");

            if ($lin_loc = gsql_fetch_array($ris_loc)) {
                $lin_m['Des_Localita_Nas'] = $lin_loc['Des_Localita'];
                $lin_m['Provincia_Nas'] = $lin_loc['Sig_Prov'];
            }
        }

        $lin_m['Data_Nascita'] = rtrim($lin_m['Data_Nascita']);


        if (!$lin_m['Cod_Nazione']) {
            $lin_m['Cod_Nazione'] = COD_NAZIONE_ITALIA;
        }

        $utente_tpl->assegnaGlobali();


        /*
        if (isset($lin_m['ID_Role'])) {
            if ($lin_m['ID_Role'] > 0 && $lin_m['ID_Role']!=7 && $lin_m['ID_Role']!=8) {
                $lin_m['ID_Role'] = 1;
            }
        }*/

        $utente_tpl->assign("utente", $lin_m);
        //selezione tutti i ruoli in modo da visualizzarli nel template


        $vett_ruoli = Roles::caricaRuoli();

        foreach ($vett_ruoli as $k => $v) {
            $vett_temp = array('Progressivo' => $k, 'Descrizione' => $v);
            $utente_tpl->append('ruoli', $vett_temp);
        }


        $utente_tpl->assign('reparti', \Gerp\Utenti\RepartiRepository::fetchAll());

        $obj_azienda = new Gerp_Azienda();

        $obj_azienda->caricaNazioni($utente_tpl, true);

        $utente_tpl->assign("ragione_sociale", correggi_input_html(carica_des_cliente($lin_m['ID_Cliente_Utente'])));

        $vett_stored = (array)json_decode($this->vett_dati['Json_Utente'], true);

        $vett_param = explode(',', Gerp_Config::get('amm_ute', 'custom_fields'));

        $utente_tpl->assign('altri_dati', Gerp\Core\AttributeHelper::fetchMergeAttributes($vett_stored, $vett_param));

        $utente_tpl->display("utenti/utente_anagrafica.tpl");


    } // end modifica


    public function visualizzaMenu($sub = 'dati')
    {

        $utente = $this->id;

        $menu = [
            ["sub" => "dati", "voce" => trans("Anagrafica")],
            ["sub" => "auth", "voce" => trans("Autenticazione")],
            ["sub" => "operations", "voce" => trans("Operazioni")],
        ];


        if (Gerp_Config::get('amm_ute', 'directory')) {
            array_push($menu, array("sub" => "fileManager", "voce" => "Files"));
        }

        if (Gerp_Config::get('amm_ute', 'ab_work_contracts')) {
            array_push($menu, array("sub" => "rapporti", "voce" => trans("Assunzioni")));
            array_push($menu, array("sub" => "costo", "voce" => trans("Costo orario")));
        }


        if (Gerp_Config::hasModule('timbra') || Gerp_Config::hasModule('timbro')) {
            array_push($menu, array("sub" => "timb", "voce" => trans("Turni e timbrature")));
        }


        //Pannello Agente. Visualizzato solo se all'utente è assegnato un Codice Agente
        $ris = gsql_query("SELECT Codice_Agente FROM PRE_Agenti WHERE ID_Utente = '$utente' ");
        if ($lin = gsql_fetch_array($ris)) {
            array_push($menu, array("sub" => "agente", "voce" => trans("Agente")));
        }


        $tpl_menu = new Gerp_Template();
        $tpl_menu->assegnaGlobali();
        $tpl_menu->assign("utente", $utente);
        $tpl_menu->assign("sub", $sub);


        $tpl_menu->assign("menu", $menu);
        $tpl_menu->display("utenti/ute_menu.tpl");

    }

    public function elimina()
    {

        $id_utente = $this->id;

        if (!$id_utente) {
            return false;
        }


        $ris_check = gsql_query("SELECT * FROM PRE_Agenti WHERE ID_Utente = '$id_utente'; ");
        if ($lin_check = gsql_fetch_assoc($ris_check)) {
            gerp_display_error("Impossibile cancellare l'utente %d, collegato a codice agente: %s", [$id_utente, $lin_check['Codice_Agente']]);
            return false;
        }


        $ris_check = gsql_query("SELECT * FROM PRE_Preventivi WHERE Utente = '$id_utente'; ");
        if ($lin_check = gsql_fetch_assoc($ris_check)) {
            gerp_display_error("Impossibile cancellare l'utente %d, collegato al documento: %s", [$id_utente, \Gerp\Preventivi\Url::buildLinkFromArray($lin_check)]);
            return false;
        }

        $ris = gsql_query("SELECT * FROM GRA_Progetti WHERE ID_Responsabile = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            gerp_display_error("Impossibile cancellare l'utente %d, collegato al progetto: %s", [$id_utente, \Gerp\Projects\Url::buildLinkFromArray($lin)]);
            return false;
        }


        $ris = gsql_query("SELECT * FROM MAG_Movimenti WHERE Utente = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }
        /*
        $ris = gsql_query("SELECT * FROM ART_Articoli WHERE Utente = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }*/

        $ris = gsql_query("SELECT * FROM INT_Timbrature WHERE Operatore = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }

        $ris = gsql_query("SELECT * FROM INT_Timbrature WHERE Utente = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }

        $ris = gsql_query("SELECT * FROM INT_Utenti WHERE Utente = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }

        $ris = gsql_query("SELECT * FROM GRA_Lavori2 WHERE Grafico = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }

        $ris = gsql_query("SELECT * FROM PRO_Macchine_Lavori WHERE Operatore = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }


        $ris = gsql_query("SELECT * FROM IMP_Impegni WHERE Utente = '$id_utente'; ");
        if ($lin = gsql_fetch_array($ris)) {
            return false;

        }


        $this->eliminaAuth($id_utente);

        gsql_query("DELETE FROM INT_Utenti WHERE ID = '$id_utente' ");

        return true;

    }

    public function carica()
    {
        if (!$this->id) {
            return false;
        }

        $ris = gsql_query("SELECT * FROM INT_Utenti WHERE ID = '$this->id'");
        if (!$lin = gsql_fetch_array($ris)) {

            return false;

        }

        $this->vett_dati = $lin;

        return true;

    }

    public function getAsArray()
    {
        $vett = array();

        $vett['salutation'] = $this->vett_dati['Titolo'];
        $vett['name'] = $this->vett_dati['Nome'];
        $vett['surname'] = $this->vett_dati['Cognome'];

        $vett['email'] = $this->vett_dati['Email'];

        $vett['city'] = $this->vett_dati['Des_Localita'];
        $vett['address'] = $this->vett_dati['Indirizzo'];
        $vett['country'] = $this->vett_dati['Cod_Nazione'];
        $vett['phone'] = $this->vett_dati['Fisso'];
        $vett['codice_fiscale'] = $this->vett_dati['Codice_Fiscale'];
        $vett['mobile'] = $this->vett_dati['Mobile'];
        $vett['cap'] = $this->vett_dati['Cod_Cap'];

        $vett['company_name'] = $this->vett_dati['Des_Azienda'];


        return $vett;

    }

    //TODO sostituisce carica_des_utente
    public function getDescription()
    {

    }

    public function caricaAuth()
    {
        $utente = $this->id;

        $ris_m = gsql_query("SELECT  INT_Utenti_Aut.* FROM INT_Utenti_Aut
                WHERE ID_Utente='$utente'");

        if ($lin_m = gsql_fetch_assoc($ris_m)) {

            $this->vett_auth = $lin_m;
        }

    }

    public function visualizza()
    {

        $tpl = new Gerp_Template();
        $tpl->assegnaGlobali();

        if (!$this->carica()) {

            return false;

        }
        $tpl->assign("utente", $this->vett_dati);

        // precedente e successivo
        $tpl->display("utenti/testata.tpl");
        return true;


    }


    public function salva($vett)
    {
        global $obj_utente;

        if (is_object($obj_utente)) {
            $user = $obj_utente->user;
        }
        $vett_attributes = [];

        $vett_params = array(
            'nome' => '',
            'cognome' => '',
            'codice_fiscale' => '',
            'data_nascita' => '',
            'indirizzo' => '',
            'indirizzo2' => '',
            'cod_localita' => false,
            'des_localita' => false,
            'cod_cap' => false,
            'sig_prov' => false,
            'des_azienda' => '',
            'cod_localita_nas' => false,
            'des_localita_nas' => '',
            'fisso' => '',
            'mobile' => '',
            'interno' => '',
            'titolo' => '',
            'email' => false,
            'email2' => false,
            'cod_nazione' => 'IT',
            'cliente' => false,
            'id_role' => 1,
            'id_reparto' => 1,
            'stato_utente' => 1,
            'note_utente' => '',
            'azienda' => 'P',
            'force_id' => null,
            'vett_attributes' => array()
        );


        foreach ($vett_params as $k => $v) {

            if (isset($vett[$k]) && is_array($vett[$k])) {

                $$k = $vett[$k];

            } elseif (isset($vett[$k])) {

                $$k = trim(($vett[$k]));

            } else {

                $$k = $vett_params[$k];
            }

        }


        if ($data_nascita && !\Opengerp\Utils\Filters::isDateDaysMonthYear($data_nascita)) {

            \Opengerp\Core\Console\Console::appendError("Data di nascita in un formato non valido");
            return false;

        }


        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \Opengerp\Core\Console\Console::appendError('Email non valida.');
            $email = false;

        }

        if ($email2 && !filter_var($email2, FILTER_VALIDATE_EMAIL)) {
            \Opengerp\Core\Console\Console::appendError('Email 2 non valida.');
            $email2 = false;

        }

        $stato_utente = \Opengerp\Utils\Strings\Filters::filterInt($stato_utente);

        if (!($stato_utente)) {
            \Opengerp\Core\Console\Console::appendError('Stato non valido.');

            return false;
        }


        $utente = $this->id;

        $str_activation = \Opengerp\Modules\Users\Domain\PasswordHelper::gerp_make_password();

        //recovery of user parameters e.g. theme
        if ($vett_utente = \Opengerp\Modules\Users\Repository\UsersRepository::fetchById($utente)) {

            $param_utente = json_decode($vett_utente['Json_Utente'], true);
            $vett_attributes = array_merge($param_utente, $vett_attributes);
        }

        $json_utente = (json_encode($vett_attributes));


        // inserimento
        $dbo = new \Opengerp\Core\DbObjects\User();

        $dbo->Nome = $nome;
        $dbo->Cognome = $cognome;

        $dbo->Titolo = $titolo;

        $dbo->Indirizzo = $indirizzo;
        $dbo->Indirizzo2 = $indirizzo2;
        $dbo->Codice_Fiscale = $codice_fiscale;
        $dbo->Data_Nascita = $data_nascita;
        $dbo->Azienda = $azienda;
        $dbo->Des_Azienda = $des_azienda;

        $dbo->Cod_Cap = $cod_cap;
        $dbo->Sig_Prov = $sig_prov;
        $dbo->Cod_Localita = $cod_localita;
        $dbo->Cod_Localita_Nas = $cod_localita_nas;
        $dbo->Des_Localita = $des_localita;
        $dbo->Des_Localita_Nas = $des_localita_nas;

        $dbo->Fisso = $fisso;
        $dbo->Mobile = $mobile;
        $dbo->Interno = $interno;
        $dbo->Email = $email;
        $dbo->Email_Intranet = $email2;
        $dbo->Note_Utente = $note_utente;
        $dbo->ID_Role = $id_role;
        $dbo->ID_Reparto = $id_reparto;
        $dbo->Cod_Nazione = $cod_nazione;
        $dbo->ID_Cliente_Utente = $cliente ?? null;
        $dbo->Stato_Utente = $stato_utente;
        $dbo->Data_Ultimo_Agg = date('Y-m-d H:i:s');;
        $dbo->Json_Utente = $json_utente;
        $dbo->Utente = $user;


        $dbo->Str_Activation = $str_activation;

        if (!$utente || $force_id) {

            if ($force_id) {
                $dbo->ID = $force_id;
            }

            $dbo->Date_Inserted = date('Y-m-d H:i:s');
            $dbo->insert();
            $this->id = $dbo->ID;

        } else {
            $dbo->ID = $utente;
            $dbo->update();
        }


        return true;

    }

    public function checkActivation($str_check)
    {

        if ($this->vett_dati['Stato_Utente'] != 2) {
            return true;
        }

        if ($this->vett_dati['Str_Activation'] == $str_check) {
            return true;
        }

        return false;

    }

    public function archivia()
    {
        global $obj_utente;
        $user = $obj_utente->getUserId();

        $utente = $this->id;

        $ris_check = gsql_query("SELECT * FROM PRO_Macchine_Operatori WHERE ID_Operatore = '$utente'");
        if ($lin_check = gsql_fetch_assoc($ris_check)) {

            gerp_display_error("Operatore associato alla risorsa $lin_check[ID_Macchina], operazione non consentita.");
            return false;

        }

        //nel caso di utente eliminato, nella tabella utenti viene impostato il flag eliminato su 1
        //in questo modo l'utente viene escluso dal menu'
        gsql_query("UPDATE INT_Utenti SET Stato_Utente=" . ID_STATO_UTENTE_ELIMINATO . ",
            Utente='" . $user . "', Data_Ultimo_Agg=" . SQL_GETDATE . "
            WHERE ID=" . $utente . "");

        $this->eliminaAuth($utente);


    }


    public function modificaAuth($utente)
    {
        global $obj_utente;

        $ris_m = gsql_query("SELECT INT_Utenti_Aut.* FROM INT_Utenti_Aut WHERE ID_Utente='$utente'");

        if (!($lin_m = gsql_fetch_assoc($ris_m))) { // controlla se all'utente sono stati immessi di dati relativi all'autenticazione

            $ris_ut = gsql_query("SELECT * FROM INT_Utenti WHERE ID='$utente'");
            $lin_ut = correggi_input_html(gsql_fetch_array($ris_ut));

            if ($lin_ut['Email']) {
                $lin_m["Username"] = $lin_ut['Email'];
            } else {
                $nome = clean_string_user_pass($lin_ut['Nome']);
                $cognome = clean_string_user_pass($lin_ut['Cognome']);

                $lin_m["Username"] = $nome . $cognome;
            }

            $lin_m["Cod_Lingua"] = 'IT';

            // password di default
            $lin_m["Password"] = gerp_make_password(9);

            //fa in modo che la casella di testo username sia abilitata alla scrittura
            $lin_m["auth"] = false;

        } else {
            //fa in modo che la casella di testo username sia disabilitata alla scrittura in modo che lo username non venga modificato
            $lin_m["auth"] = true;
        }

        $tpl = new Gerp_Template();

        $lin_m['ID'] = $utente;

        $tpl->assegnaGlobali();

        if (\Gerp\Utenti\Authenticator::verifyBlockedUser($utente, false)) {
            $tpl->assign('utente_bloccato', true);
        }
        $tpl->assign("utente", $lin_m);

        $tpl->assign("dataora_lastlogin", formatta_data($lin_m['Lastlogin']) . " " . formatta_tempo($lin_m['Lastlogin']));
        $tpl->assign("last_operation", Gerp_Log::fetchLastOperation($utente));


        $vett_parametri = array();

        $ris_mu = gsql_query("SELECT * FROM INT_Utenti_Moduli WHERE ID_Utente='$utente' ");

        while ($lin_mu = gsql_fetch_assoc($ris_mu)) {
            $vett_moduli[$lin_mu['ID_Modulo']] = true;
            $vett_parametri[$lin_mu['ID_Modulo']] = $lin_mu['Parametri'];

        }

        $vett_parent = Gerp_Auth::caricaVociMenuParent($obj_utente->vett_auth['Cod_Lingua']);

        $str_sql = "SELECT * FROM INT_Moduli ORDER BY ID_Parent, Descrizione ";

        $ris_d = gsql_query($str_sql);

        $id_parent_prec = null;

        $vett_cfg_parametri = Gerp_System_Helper::caricaStrutturaParametriUtenti();

        $loader = new \Gerp\System\ModuliRepository();
        $modules = $loader->fetchAll();

        while ($lin_d = gsql_fetch_assoc($ris_d)) {

            if (isset($vett_cfg_parametri[$lin_d['ID']])) {
                $lin_d['vett_cfg_params'] = $vett_cfg_parametri[$lin_d['ID']];
            }

            if (isset($modules[$lin_d['ID']])) {
                $lin_d['Des_Ext'] = $modules[$lin_d['ID']]['Des_Ext'];
            }

            if (isset($vett_parametri[$lin_d['ID']])) {
                parse_str($vett_parametri[$lin_d['ID']], $lin_d['parametri']);
            }

            if ($id_parent_prec != $lin_d['ID_Parent']) {
                $lin_d['inte'] = true;
                $id_parent_prec = $lin_d['ID_Parent'];

                if (isset($vett_parent[$lin_d['ID_Parent']]['Voce_Menu'])) {
                    $lin_d['Des_Ruolo'] = $vett_parent[$lin_d['ID_Parent']]['Voce_Menu'];
                }
            }

            //seleziona tutti i moduli associati all'utente in caso di modifica
            if (isset($vett_moduli[$lin_d['ID']])) {
                //spunta tutte le checkbox selezionate
                $lin_d['check_ruolo'] = 'checked';
                $lin_d['Parametri'] = rtrim($vett_parametri[$lin_d['ID']]);
            }

            $tpl->append("moduli", $lin_d);
        }

        $resource_repository = new \Gerp\Core\ResourceRepository();

        $resources_data = $resource_repository->fetchAll();

        $resources = [];

        foreach ($resources_data as $resource) {

            $resources[$resource['Progressivo']] = $resource['Descrizione'];

        }

        $tpl->assign('resources', $resources);

        $department_repository = new \Gerp\Utenti\RepartiRepository();

        $departments_data = $department_repository->fetchAll();

        $departments = [];

        foreach ($departments_data as $department) {

            $departments[$department['ID_Reparto']] = $department['Descrizione'];

        }

        $tpl->assign('departments', $departments);

        $obj_azienda = new Gerp_Azienda();
        $tpl->assign("vett_lingue", $obj_azienda->caricaLingue());

        //visualizza il template
        $tpl->display("utenti/utente_autenticazione.tpl");
    }


    public function updatePassword($utente)
    {
        $tpl = new Gerp_Template();
        $tpl->assegnaGlobali();


        $tpl->assign('utente', $utente);
        $tpl->display("utenti/cambio_password.tpl");

    }


    public function salvaPassword($vett)
    {
        $this->plain_password = $vett['str_new_pwd'];
        $str_password = password_hash($vett['str_new_pwd'], PASSWORD_DEFAULT);

        $utente = $vett['utente'];

        $str_sql = "UPDATE INT_Utenti_Aut
            SET Password = '$str_password', Data_Cambio_Password = NOW()
            WHERE ID_Utente = '$utente' ";

        gsql_query($str_sql);

        return true;

    }


    public function fatturazione($utente)
    {

        $query_cliente = gsql_query("SELECT * FROM PRE_Clienti WHERE Utente = '$utente'");

        if (!$lin_cliente = gsql_fetch_array($query_cliente)) {
            return false;
        }

        $tpl = new Gerp_Template();
        $tpl->assegnaGlobali();
        $tpl->assign('cliente', $lin_cliente);
        $tpl->display('clienti/cliente_compatto.tpl');

    }

    public function rimuovi2fa($utente)
    {
        if (!controlla_rand() || !$utente) {
            return false;
        }

        if (gsql_query("UPDATE INT_Utenti_Aut SET Authenticator_Secret = NULL WHERE ID_Utente = $utente LIMIT 1")) {
            return true;
        }

        return false;
    }

}


