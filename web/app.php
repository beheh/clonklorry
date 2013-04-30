<?php
$time = microtime(true);

error_reporting(E_ALL | E_NOTICE);

require '../vendor/autoload.php';

$lorry = new Lorry\Environment();
$lorry->handle();

$duration = round(microtime(true) - $time, 3);
echo '<p class="pull-right" style="margin-right: 20px;"><span class="muted">'.$duration.'s'.'</span></p>';