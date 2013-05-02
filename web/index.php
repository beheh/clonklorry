<?php
error_reporting(E_ALL | E_NOTICE);

require '../vendor/autoload.php';

$lorry = new Lorry\Environment();
$lorry->handle();