<?php

use Analog\Logger;

require '../app/bootstrap.php';

$lorry = null;
try {
	$lorry = new Lorry\Environment();
	$lorry->setup();
	$lorry->handle();
} catch(Exception $e) {
	// emergency logging
	$logger = new Logger();
	$logger->handler(\Analog\Handler\File::init(__DIR__.'/../logs/emergency.log'));

	$identifier = sha1(uniqid('lorry'));
	$logger->emergency($identifier.' - '.get_class($e).': '.$e->getMessage());

	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/plain');

	$config = null;
	if($lorry instanceof \Lorry\Environment) {
		$config = $lorry->getConfig();
	}
	if($config && $config->get('debug')) {
		echo 'An internal error occured: '.get_class($e).PHP_EOL.PHP_EOL;
		if($e->getMessage()) {
			echo 'Message: '.$e->getMessage().PHP_EOL.PHP_EOL;
		}
		echo 'Stack trace:'.PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
		echo 'Lorry platform in debug mode.';
	} else {
		echo 'An internal error occured. We will be looking into this.'.PHP_EOL.PHP_EOL;
		echo 'Error was '.$identifier.' at '.date('Y-m-d H:i:s').'.'.PHP_EOL;
	}
}
