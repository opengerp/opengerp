<?php

ini_set("display_errors", "on");
ini_set('display_startup_errors', '1');

error_reporting(E_ALL  && ~E_NOTICE && ~E_DEPRECATED);



require './vendor/autoload.php';
$config = require './tests/env/config.php';
$db_config = \Opengerp\App\DbConfig::fromArray($config);

$db_config->connect();