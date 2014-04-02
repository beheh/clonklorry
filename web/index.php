<?php

require '../vendor/autoload.php';

\Analog::handler(\Analog\Handler\Threshold::init(
				\Analog\Handler\File::init('../app/logs/lorry.log'),
				\Analog::WARNING
));

\Analog::debug('initializing lorry');

try {
	$lorry = new Lorry\Environment();
	$lorry->requestHandle();
} catch(Exception $e) {
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/plain');
	echo $e->getMessage();
}