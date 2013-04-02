<?php

/**
 * Add configuration values in here and write a description for them ;-)
 */
 
/**
 * Project name.
 */
$config['name'] = 'Lorry';


/**
 * Base Url of the project.
 */
$config['baseUrl'] = 'http://'.$_SERVER['SERVER_NAME'].'/';
/**
 * Debug settings
 */
$config['debug'] = array(
	'enabled' => true
);

/**
 * Database Uri for PDO.
 */
$config['database'] = array(
	'dsn' => '',
	'username' => 'root',
	'password' => ''
);

/**
 * Amazon Web Services
 */
$config['aws'] = array(
	'key' => '',
	'secret' => ''
);

/**
 * Contains additional options for the database driver.
 */
$config['databaseOptions'] = array(
);