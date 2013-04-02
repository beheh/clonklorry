<?php
error_reporting(E_ALL | E_NOTICE);

define('ROOT', __DIR__ . '/');
require 'libs/Lorry/Environment.php';

$lorry = new Lorry_Environment();
$lorry->handle();
?>