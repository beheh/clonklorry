<?php

//$time = microtime();

error_reporting(E_ALL & ~(E_STRICT));

require '../vendor/autoload.php';

try {
	$lorry = new Lorry\Environment();
	$lorry->handle();
} catch(Exception $ex) {
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/plain');
	echo 'An internal error occured:'.PHP_EOL;
	echo $ex;
}

//echo (microtime() - $time).'s';