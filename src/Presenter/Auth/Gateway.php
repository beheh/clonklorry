<?php

namespace Lorry\Presenter\Auth;

use ErrorException;
use Lorry\Presenter;
use Lorry\Exception\AuthentificationFailedException;
use Lorry\Exception\FileNotFoundException;
use LightOpenID;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

class Gateway extends Presenter {

	public function get($provider) {

		if($this->session->authenticated()) {
			$login_hint = $this->session->getUser()->getEmail();
		} else {
			$this->session->ensureSession();
			unset($_SESSION['returnto']);
			$returnto = filter_input(INPUT_GET, 'returnto');
			if($returnto) {
				$_SESSION['returnto'] = $returnto;
			}
		}
		switch($provider) {
			case 'openid':
				try {
					$openid = new LightOpenID($this->config->get('base'));
					$openid->identity = filter_input(INPUT_GET, 'identity', FILTER_VALIDATE_URL);
					$openid->realm = $this->config->get('base');
					$openid->required = array('contact/email');
					$openid->optional = array('namePerson/friendly');
					$openid->returnUrl = $this->config->get('base').'/auth/callback/openid';
					$this->redirect($openid->authUrl(), true);
				} catch(ErrorException $ex) {
					throw new AuthentificationFailedException();
				}
				break;
			case 'google':
				$google = new Google(array(
					'clientId' => $this->config->get('oauth/google/id'),
					'clientSecret' => $this->config->get('oauth/google/secret'),
					'redirectUri' => $this->config->get('base').'/auth/callback/google'
				));
				$google->scopes = array('profile', 'email');
				$custom = '';
				if($login_hint) {
					$custom .= '&login_hint='.$login_hint;
				}
				return $this->redirect($google->getAuthorizationUrl().$custom, true);
				break;
			case 'facebook':
				$facebook = new Facebook(array(
					'clientId' => $this->config->get('oauth/facebook/id'),
					'clientSecret' => $this->config->get('oauth/facebook/secret'),
					'redirectUri' => $this->config->get('base').'/auth/callback/facebook'
				));
				$facebook->scopes = array('public_profile', 'email');	
			$facebook->authorize();
				break;
			default:
				throw new FileNotFoundException();
				break;
		}
	}

}
