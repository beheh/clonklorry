<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;
use Lorry\Exception\AuthentificationFailedException;
use Opauth;

class Callback extends Presenter {

	public function get() {
		require '../app/config/opauth.php';
		$config['Strategy']['Google']['state'] = $this->session->getState();
		$opauth = new Opauth($config, false);
		$this->session->clearState();

		$response = null;

		switch($opauth->env['callback_transport']) {
			case 'session':
				$response = isset($_SESSION['opauth']) ? $_SESSION['opauth'] : array();
				unset($_SESSION['opauth']);
				break;
			case 'post':
				$response = isset($_POST['opauth']) ? unserialize(base64_decode($_POST['opauth'])) : array();
				break;
			case 'get':
				$response = isset($_GET['opauth']) ? unserialize(base64_decode($_GET['opauth'])) : array();
				break;
			default:
				throw new AuthentificationFailedException('Unsupported callback transport');
				break;
		}

		try {
			if(array_key_exists('error', $response)) {
				throw new AuthentificationFailedException('Response contained error field');
			}
			if(empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
				throw new AuthentificationFailedException('Missing fields in auth response');
			}
			if(!$opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
				throw new AuthentificationFailedException('Invalid auth response: '.$reason);
			}
		} catch(AuthentificationFailedException $exception) {
			if($this->session->authenticated()) {
				$this->redirect('/settings?update-oauth=failed#oauth');
				return;
			}
			throw $exception;
		}

		if($this->session->authenticated()) {
			// we now trust provider and user
			$user = $this->session->getUser();

			//$user->setProvider('google', '123123123123');

			$this->redirect('/settings?update-oauth=success#oauth');
		} else {
			// grab user with openid data fitting
			//$user = $this->->byOauth('google', '123123123123');
			$user = false;

			if($user != null) {
				$this->redirect('/');
			} else {
				// none found -> registration?
				$this->redirect('/register');
			}
		}
	}

}
