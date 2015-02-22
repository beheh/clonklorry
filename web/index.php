<?php

use Analog\Logger;

require '../app/bootstrap.php';

$lorry = null;
try {
	$lorry = new Lorry\Environment();
	$lorry->setup();
	$lorry->handle();
} catch(Exception $e) {

	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/plain');

	echo 'An internal error occured: '.get_class($e).PHP_EOL.PHP_EOL;
	if($e->getMessage()) {
		echo 'Message: '.$e->getMessage().PHP_EOL.PHP_EOL;
	}
	echo 'Stack trace:'.PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
}
