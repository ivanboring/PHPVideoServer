<?php

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once('config.php');
require_once('includes/autoload.php');

$core = new core($config);

?>