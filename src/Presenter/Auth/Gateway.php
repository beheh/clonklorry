<?php

namespace Lorry\Presenter\Auth;

use ErrorException;
use Lorry\Presenter;
use Lorry\Exception\AuthentificationFailedException;
use Lorry\Exception\FileNotFoundException;
use LightOpenID;
use League\OAuth2\Client\Provider\Google;
use Lorry\Override\Facebook;

class Gateway extends Presenter {

	public function get($provider) {

		try {
			$login_hint = false;
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
						if(!$this->session->authenticated()) {
							$openid->optional = array('namePerson/friendly', 'contact/email');
						}
						$openid->returnUrl = $this->config->get('base').'/auth/callback/openid';
						$this->redirect($openid->authUrl(), true);
					} catch(ErrorException $ex) {
						throw new AuthentificationFailedException($ex->getMessage());
					}
					break;
				case 'google':
					$scopes = array('profile');
					if(!$this->session->authenticated()) {
						$scopes[] = 'email';
					}
					$google = new Google(array(
						'clientId' => $this->config->get('oauth/google/id'),
						'clientSecret' => $this->config->get('oauth/google/secret'),
						'redirectUri' => $this->config->get('base').'/auth/callback/google',
						'scopes' => $scopes
					));
					$custom = '';
					if($login_hint) {
						$custom .= '&login_hint='.$login_hint;
					}
					$authorizationUrl = $google->getAuthorizationUrl();
					$this->session->setAuthorizationState($google->state);
					$this->redirect($authorizationUrl, true);
					break;
				case 'facebook':
					$scopes = array('public_profile');
					if(!$this->session->authenticated()) {
						$scopes[] = 'email';
					}
					$facebook = new Facebook(array(
						'clientId' => $this->config->get('oauth/facebook/id'),
						'clientSecret' => $this->config->get('oauth/facebook/secret'),
						'redirectUri' => $this->config->get('base').'/auth/callback/facebook',
						'scopes' => $scopes
					));
					$authorizationUrl = $facebook->getAuthorizationUrl();
					$this->session->setAuthorizationState($facebook->state);
					$this->redirect($authorizationUrl, true);
					break;
				default:
					throw new FileNotFoundException();
					break;
			}
		} catch(AuthentificationFailedException $exception) {
			if($this->session->authenticated()) {
				$this->logger->error($exception);
				return $this->redirect('/settings?update-oauth=failed#oauth');
			}
			throw $exception;
		}
	}

}
