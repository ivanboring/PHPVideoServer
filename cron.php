<?php

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_time_limit(0);

require_once('config.php');
require_once('includes/autoload.php');

$core = new core($config);

$index = new indexing();
$index->index();

?>