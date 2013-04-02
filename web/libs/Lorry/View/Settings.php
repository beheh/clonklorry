<?php

require ROOT.'libs/LightOpenID/openid.php';

class Lorry_View_Settings extends Lorry_View {

	protected function render() {
		if(isset($_GET['openid'])) {
			$openid = new LightOpenID('localhost');
			$openid->realm = (!empty($_SERVER['HTTPS']) ? 'https' : 'http').'://localhost';
			$openid->returnUrl = $openid->realm.'/lorry/settings/';
			$openid->identity = 'https://www.google.com/accounts/o8/id';
			header('Location: '.$openid->authUrl());
			exit();
		}
		return $this->lorry->twig->render('settings.twig');
	}

	protected function allow() {
		return $this->lorry->session->authenticated();
	}

}

