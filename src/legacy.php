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

