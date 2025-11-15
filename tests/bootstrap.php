<?php

ini_set("display_errors", "on");
ini_set('display_startup_errors', '1');

error_reporting(E_ALL  && ~E_NOTICE && ~E_DEPRECATED);



require './vendor/autoload.php';
$config = require './tests/env/config.php';
$db_config = \Opengerp\App\DbConfig::fromArray($config);

$conn = $db_config->connect();

$db = new \Opengerp\Database\Db( new \Opengerp\Database\MysqliAdapter($conn) );

\Opengerp\Database\DbObject::setDefaultDb($db);

$dbName_hot = $db_config->getDbName();

$db->query("DROP DATABASE IF EXISTS $dbName_hot");
$db->query("CREATE DATABASE $dbName_hot");
$conn->select_db($dbName_hot);

$update = new \Opengerp\Utils\Database\SchemaUpdater($db);

$update->setPreviewUpdateOff();
$update->checkDatabaseSchema("./database/schema/schema.xml");
