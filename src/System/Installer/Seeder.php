<?php

namespace Opengerp\System\Installer;

use Opengerp\Database\DbObject;
use \Opengerp\Modules\Users\Service\Utente as Utente;
class Seeder
{

    public static function runSqlFileWithQuery(\Opengerp\Database\Db $db, string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException("SQL file not readable: $path");
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new \RuntimeException("Cannot read SQL file: $path");
        }

        // Rimuove commenti /* ... */
        $sql = preg_replace('~/\*.*?\*/~s', '', $sql);
        // Rimuove commenti di riga che iniziano con --
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        // Split su ; solo fuori da apici
        $statements = [];
        $buf = '';
        $inSingle = false;
        $inDouble = false;

        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];

            // Gestisce caratteri escapati dentro stringhe
            if (($inSingle || $inDouble) && $ch === '\\' && $i + 1 < $len) {
                $buf .= $ch . $sql[$i + 1];
                $i++;
                continue;
            }

            if (!$inDouble && $ch === "'") $inSingle = !$inSingle;
            if (!$inSingle && $ch === '"') $inDouble = !$inDouble;

            if (!$inSingle && !$inDouble && $ch === ';') {
                $stmt = trim($buf);
                if ($stmt !== '') $statements[] = $stmt;
                $buf = '';
                continue;
            }

            $buf .= $ch;
        }

        $last = trim($buf);
        if ($last !== '') $statements[] = $last;

        // Esecuzione con query()
        foreach ($statements as $idx => $stmt) {

            $db->query($stmt);

        }
    }


    public static function populateStardardTables()
    {


        $db = DbObject::getDefaultDb();

        self::runSqlFileWithQuery($db, './database/scripts/accounting.sql');
        self::runSqlFileWithQuery($db, './database/scripts/countries.sql');


        /*
        $documents = simplexml_load_file($include_prefix.'lib/schema/gerp_documents.xml');

        if (!$documents) {
            throw new \Exception('File gerp_documents.xml non trovato');
        }

        \Gerp\System\ModuliRepository::checkDocuments($documents);
        */


        self::insertDefaultPaymentTerms();
        self::insertFirstFiscalYear();

    }
    public static function insertDefaultPaymentTerms()
    {

        $obj = new \Opengerp\Modules\Accounting\DbObjects\PaymentTerms();
        $obj->Cod_Pagamento = 'RD';
        $obj->Des_Pagamento = 'Rimessa diretta';
        $obj->Cod_Tipo_Pagamento = 'RD';
        $obj->Num_Giorni = 0;
        $obj->Default_Pagamento = 1;
        $obj->Perc_Sconto_Pagamento = 0;
        $obj->Ab_Controllo_Saldo = 0;

        $obj->insert();

        \Opengerp\Core\Console\Console::appendSuccess('Condizione di pagamento default: ok');



    }

    public static function insertFirstFiscalYear()
    {

        $esercizio = new \Opengerp\Modules\Accounting\DbObjects\AccountsFiscalYear();
        $esercizio->Num_Esercizio = date('Y');
        $esercizio->Data_Inizio_Esercizio = date('Y-m').'-01';
        $esercizio->Utente = 0;
        if ($esercizio->insert()) {
            \Opengerp\Core\Console\Console::appendSuccess('Esercizio nuovo: ok');

        }

    }



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