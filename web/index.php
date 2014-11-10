<?php
require '../app/bootstrap.php';

try {
	$lorry = new Lorry\Environment();
	$lorry->requestHandle();
} catch(Exception $e) {
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/plain');
	echo $e->getMessage();
}