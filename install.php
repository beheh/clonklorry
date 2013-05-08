#!/usr/bin/php
<?php
$fail = false;

echo 'checking php version... ';
if(version_compare(PHP_VERSION, '5.3.0', '>')) {
	echo 'compatible';
} else {
	$fail = true;
	echo 'incompatible';
}
echo ' ('.PHP_VERSION.')'.PHP_EOL;

echo 'checking extensions... ';
$extensions = array('json', 'gettext', 'pdo', 'http', 'openssl');
$loaded = 0;
foreach($extensions as $extension) {
	if(extension_loaded($extension)) {
		$loaded++;
	} else {
		echo 'missing extension "'.$extension.'"'.PHP_EOL;
		$fail = true;
	}
}
echo 'extension check complete, ';
if($loaded == count($extensions)) {
	echo 'all present ('.count($extensions).')'.PHP_EOL;
} else {
	echo 'missing '.(count($extensions) - $loaded).' extension(s)'.PHP_EOL;
}

echo PHP_EOL;
if(!$fail) {
	echo 'all checks passed, environment compatible';
} else {
	echo 'some checks failed, environment incompatible';
}