<?php

ini_set("display_errors", "on");
ini_set('display_startup_errors', '1');

error_reporting(E_ALL  && ~E_NOTICE && ~E_DEPRECATED);



require './vendor/autoload.php';

$config_path = './tests/env/config.php';
if (!file_exists($config_path)) {
    fwrite(STDERR, "\033[31mERRORE: file mancante $config_path\033[0m\n");
    exit(1);
}

$config = require $config_path;

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


$base_modules_repo = new \Opengerp\System\ModuliRepository('./src/Modules/Users/Config/module.xml');
$base_modules_repo->checkModules();

\Opengerp\System\MenuItems::checkTableMenu('./config/modules/core.menu.xml');
\Opengerp\System\Installer\Seeder::checkAdministratorUser();
\Opengerp\System\Installer\Seeder::populateStardardTables();
