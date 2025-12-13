<?php

namespace Opengerp\System\Installer;

use \Opengerp\Modules\Users\Service\Utente as Utente;
class Seeder
{


    public static function checkAdministratorUser()
    {
        echo "\nCheck administrator...";

        $obj_ut = new Utente();

        $obj_ut->id = 1;
        if (!$obj_ut->carica()) {

            echo "\nCreate administrator...";

            $vett['nome'] = 'Assistenza';
            $vett['cognome'] = 'Gerp';
            $vett['force_id'] = 1;
            $vett['id_role'] = 0;


            $obj_ut->salva($vett);


            $vett_auth['str_username'] = 'admin';
            $vett_auth['str_password'] = 'admin';
            $vett_auth['ab_internet'] = 1;
            $vett_auth['ab_vis_costi'] = 1;

            $vett_auth['vett_moduli'][] = 'amm_ute';
            $vett_auth['vett_moduli'][] = 'tools';
            $vett_auth['vett_moduli'][] = 'config';
            $vett_auth['vett_moduli'][] = 'amm_cli';
            $vett_auth['vett_moduli'][] = 'pwd';
            $vett_auth['vett_moduli'][] = 'cron';

            $obj_ut->salvaAuth(1, $vett_auth);

        }

    }




}