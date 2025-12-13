<?php

namespace Opengerp\Modules\Users\Repository;

use Gerp_Config;
use Gerp_System_Files;

class UsersRepository
{
    public static function fetchById($user_id)
    {
        $db = \Opengerp\Database\DbObject::getDefaultDb();

        $user_id = $db->escape_string($user_id);

        $result = $db->query("SELECT
        INT_Utenti_Aut.*, INT_Utenti.*,INT_Utenti_Aut.ID_Utente
        FROM INT_Utenti  LEFT JOIN INT_Utenti_Aut ON INT_Utenti_Aut.ID_Utente = INT_Utenti.ID

        WHERE INT_Utenti.ID = '$user_id' ");

        if (!$line = $result->fetch()) {
            return false;
        }

        if (!array_key_exists('Parametri_Aut', $line) || !$line['Parametri_Aut']) {
            $line['parametri_aut'] = '{}';
        } else {
            $line['parametri_aut'] = json_decode($line['Parametri_Aut'], true);
        }

        return $line;
    }


    public static function fetchByUsername($username)
    {
        $db = \Opengerp\Database\DbObject::getDefaultDb();

        $username = $db->escape_string($username);

        $result = gsql_query("SELECT ID_Utente,Username,Ab_Internet,Password,Lastlogin,Nome,Cognome,ID_Role,Email,Email_Intranet,
            Stato_Utente, Tipo_Menu,INT_Utenti_Aut.*, INT_Utenti.*
            FROM INT_Utenti_Aut INNER JOIN INT_Utenti ON INT_Utenti_Aut.ID_Utente = INT_Utenti.ID
            WHERE (Username = '$username' OR Email = '$username') AND INT_Utenti.Stato_Utente = 1 ");


        if (!$line = gsql_fetch_assoc($result)) {
            return false;
        }

        return $line;
    }


    public static function fetchAllActive()
    {
        $vett_utenti = array();

        $str_query = "SELECT * FROM INT_Utenti WHERE Stato_Utente <> 99 ";
        $str_query .= " ORDER BY Cognome,Nome ";

        $ris_utenti = gsql_query($str_query);

        while ($lin_utenti = correggi_input_html(gsql_fetch_assoc($ris_utenti))) {

            if (Gerp_Config::get('amm_ute', 'ab_nome_cognome')) {
                $str = $lin_utenti['Titolo'] . " " . $lin_utenti['Nome'] . " " . $lin_utenti['Cognome'];
            } else {
                $str = $lin_utenti['Cognome'] . " " . $lin_utenti['Nome'];
            }


            if ($lin_utenti['ID_Role'] > 7 || ($lin_utenti['Cognome'] == '' && $lin_utenti['Nome'] == '')) {
                $str .= ' ' . $lin_utenti['Des_Azienda'];
            }


            $lin_utenti['Des_Utente'] = $str;

            $vett_utenti[] = $lin_utenti;
        }

        return $vett_utenti;
    }


    public static function fetchByDepartment($id_reparto)
    {
        $vett_utenti = array();

        if (!$id_reparto = \Gerp\Utils\Filters::filterInt($id_reparto)) {
            return $vett_utenti;
        }


        $str_query = "SELECT * FROM INT_Utenti WHERE ID_Reparto = '$id_reparto' AND  Stato_Utente <> 99 ";
        $str_query .= " ORDER BY Cognome,Nome ";

        $ris_utenti = gsql_query($str_query);

        while ($lin_utenti = correggi_input_html(gsql_fetch_assoc($ris_utenti))) {

            if (Gerp_Config::get('amm_ute', 'ab_nome_cognome')) {
                $str = $lin_utenti['Titolo'] . " " . $lin_utenti['Nome'] . " " . $lin_utenti['Cognome'];
            } else {
                $str = $lin_utenti['Cognome'] . " " . $lin_utenti['Nome'];
            }

            $lin_utenti['Des_Utente'] = $str;

            $vett_utenti[] = $lin_utenti;
        }

        return $vett_utenti;
    }

    public static function fetchByDepartments(string $ids): array
    {
        $users = [];

        $query = "SELECT *
        FROM INT_Utenti
        WHERE ID_Reparto IN ($ids)
            AND Stato_Utente <> 99";

        $result = gsql_query($query);

        while ($row = correggi_input_html(gsql_fetch_assoc($result))) {
            if (Gerp_Config::get('amm_ute', 'ab_nome_cognome')) {
                $user_description = $row['Titolo'] . " " . $row['Nome'] . " " . $row['Cognome'];
            } else {
                $user_description = $row['Cognome'] . " " . $row['Nome'];
            }

            $row['Des_Utente'] = $user_description;

            $users[] = $row;
        }

        return $users;
    }


    public static function countActiveUsers($return_results = false)
    {
        $ris = gsql_query("SELECT * FROM INT_Utenti_Aut WHERE ID_Utente <> 1");

        if ($return_results) {
            return gsql_fetch_all($ris);
        }

        return gsql_num_rows($ris);
    }

    public static function findByEmail($email)
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }


        $email = doppio_apice($email);
        $ris = gsql_query("SELECT * FROM INT_Utenti WHERE Email = '$email' ");
        if (!$lin = gsql_fetch_assoc($ris)) {
            return false;
        }


        return $lin['ID'];

    }

    public static function checkEmailUnique($email): bool
    {
        if (self::findByEmail($email) > 0) {
            return false;
        }

        return true;


    }

    public static function findByBusinessPartner($id_cliente)
    {
        if (!$id_cliente) {
            return false;
        }

        $id_cliente = gerp_escape_string($id_cliente);

        $ris = gsql_query("SELECT ID FROM INT_Utenti WHERE ID_Cliente_Utente = '$id_cliente' ");
        if ($lin = gsql_fetch_assoc($ris)) {
            return $lin['ID'];
        }

        return false;

    }

    public static function getEmail($id)
    {
        if (!$id) {
            return false;
        }

        $ris = gsql_query("SELECT Email FROM INT_Utenti WHERE ID = '$id' ");
        if (!$lin = gsql_fetch_assoc($ris)) {
            return false;

        }

        return trim($lin['Email']);


    }

    public static function getUsersDescription($str_ids)
    {
        $vett = explode(',', $str_ids);


        $str = '';
        foreach ($vett as $id) {

            $str .= carica_des_utente($id);
            $str .= ', ';
        }


        $str = substr($str, 0, -2);
        return $str;


    }

    public static function getProfileImage($user_id)
    {
        $path = Gerp_Config::get('amm_ute', 'directory');
        if (!$path) {
            return null;
        }

        $filename = Gerp_System_Files::addSlashAfterDir($path) . $user_id . "/" . "$user_id.jpg";

        if (file_exists($filename)) {

            return $filename;
        }
    }


}
