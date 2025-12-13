<?php


function gsql_query(string $query) : \Opengerp\Database\DbResult
{

    $db = \Opengerp\Database\DbObject::getDefaultDb();
    return $db->query($query);

}

function gsql_fetch_assoc(\Opengerp\Database\DbResult $ris)
{

    return $ris->fetch();

}
function gsql_fetch_array(\Opengerp\Database\DbResult $ris)
{

    return $ris->fetch();

}

function trans($string, $params = [])
{
    global $obj_translator;

    $obj_utente = \Opengerp\App\UserContext::getUser();


    if ( ! $string ) {
        return false;
    }

    if ( $obj_translator ) {
        return $obj_translator->trans($string, $params);
    }

    if ( ! $obj_utente ) {
        return $string;
    }
    if (!method_exists($obj_utente, 'getTranslator')) {
        return $string;
    }
    $translator = $obj_utente->getTranslator();
    if (!$translator) {
        return $string;
    }

    return $translator->trans($string, $params);
}