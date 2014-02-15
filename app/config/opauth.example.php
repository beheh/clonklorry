<?php

$config = array(
	'path' => '/auth/gateway/',
	'callback_url' => '/auth/callback/',
	'callback_transport' => 'session',
	'security_salt' => '',
	'Strategy' => array(
		'Google' => array(
			'client_id' => '',
			'client_secret' => '',
			'scope' => 'openid email'
		)
	),
);
