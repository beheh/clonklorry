<?php

$config = array(
	'path' => '/auth/gateway/',
	'callback_url' => '/auth/callback/',
	'callback_transport' => 'session',
	'security_salt' => '',
	'Strategy' => array(
		'OpenID' => array(
			'required' => array('contact/email'),
			'optional' => array()
		),
		'Google' => array(
			'client_id' => 'YOUR_CLIENT_ID',
			'client_secret' => 'YOUR_CLIENT_SECRET',
			'scope' => 'openid email'
		),
		'Facebook' => array(
			'app_id' => 'YOUR_APP_ID',
			'app_secret' => 'YOUR_APP_SECRET',
			'scope' => 'email'
		)
	),
);
