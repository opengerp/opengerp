<?php

namespace Opengerp\App;

class CompanyContext
{

    public static function getCompanyId()
    {

        if (!defined("ID_CLIENTE_AZIENDA")) {
            $id = 1;
        } else {

            $id = ID_CLIENTE_AZIENDA;

        }
        if (!is_numeric($id)) {
            $id = 1;
        }


        return $id;
    }

    public static function getCompanyData()
    {
        $id = self::getCompanyId();

        $ris = gsql_query("SELECT * FROM PRE_Clienti WHERE ID = '$id' ");

        if (!$lin = gsql_fetch_assoc($ris)) {
            return false;
        }

        $ris_banca = gsql_query("SELECT * FROM INT_Azienda_Banche WHERE Ab_Default = 1");

        if ($lin_banca = gsql_fetch_assoc($ris_banca)) {
            $lin['vett_banca_default'] = $lin_banca;
        }

        $ris_condizioni = gsql_query("SELECT * FROM PRE_Clienti_Fornitori WHERE ID_Cliente = '$id' AND Cod_Tipo = 'C'");

        if ($lin_condizioni = gsql_fetch_assoc($ris_condizioni)) {
            $lin['vett_condizioni'] = $lin_condizioni;
        }

        return $lin;
    }



}
