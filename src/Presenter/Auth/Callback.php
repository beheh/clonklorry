<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Model\User;
use Lorry\Exception\AuthentificationFailedException;
use Lorry\Exception\FileNotFoundException;
use LightOpenID;
use ErrorException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;
use Analog;

class Callback extends Presenter {

	public function get($provider) {

		$this->session->ensureSession();
		if(isset($_SESSION['register_oauth'])) {
			unset($_SESSION['register_oauth']);
		}

		$oauth_provider = null;

		$uid = null;
		$nickname = null;
		$email = null;

		$returnto = null;
		if(isset($_SESSION['returnto'])) {
			$returnto = $_SESSION['returnto'];
			unset($_SESSION['returnto']);
		}

		try {
			switch($provider) {
				case 'openid':
					try {
						$provider_title = 'OpenID';
						$openid = new LightOpenID($this->config->get('base'));
						if($openid->mode == 'cancel') {
							return $this->redirect('/register');
						}
						if(!$openid->mode || !$openid->validate()) {
							throw new AuthentificationFailedException('OpenID validation failed (mode is "'.$openid->mode.'")');
						}
						$uid = $openid->identity;
						$attributes = $openid->getAttributes();
						if(isset($attributes['contact/email'])) {
							$email = $attributes['contact/email'];
						}
						if(isset($attributes['namePerson/friendly'])) {
							$nickname = $attributes['namePerson/friendly'];
						}
					} catch(ErrorException $ex) {
						throw new AuthentificationFailedException($ex->getMessage());
					}
					break;
				case 'google':
					$provider_title = 'Google';
					$oauth_provider = new Google(array(
						'clientId' => $this->config->get('oauth/google/id'),
						'clientSecret' => $this->config->get('oauth/google/secret'),
						'redirectUri' => $this->config->get('base').'/auth/callback/google'
					));
					break;
				case 'facebook':
					$provider_title = 'Facebook';
					$oauth_provider = new Facebook(array(
						'clientId' => $this->config->get('oauth/facebook/id'),
						'clientSecret' => $this->config->get('oauth/facebook/secret'),
						'redirectUri' => $this->config->get('base').'/auth/callback/facebook'
					));
					break;
				default:
					throw new FileNotFoundException;
					break;
			}

			if($oauth_provider) {
				if(!$this->session->verifyAuthorizationState(filter_input(INPUT_GET, 'state'))) {
					throw new AuthentificationFailedException('invalid state (csrf?)');
				}

				if(isset($_GET['error'])) {
					if(filter_input(INPUT_GET, 'error') === 'access_denied') {
						if($this->session->authenticated()) {
							return $this->redirect('/settings#oauth');
						} else {
							return $this->redirect('/register');
						}
					}
					throw new AuthentificationFailedException(filter_input(INPUT_GET, 'error'));
				}

				try {
					// uppercase Authorization_Code: workaround for https://github.com/thephpleague/oauth2-client/issues/84
					$token = $oauth_provider->getAccessToken('Authorization_Code', array('code' => filter_input(INPUT_GET, 'code')));
				} catch(\Exception $ex) {
					throw new AuthentificationFailedException('could net get access token ('.$ex->getMessage().')');
				}
				if(!$token) {
					throw new AuthentificationFailedException('missing access token');
				}
				$profile = $oauth_provider->getUserDetails($token);
				$uid = $profile->uid;
				$nickname = $profile->nickname;
				$email = $profile->email;
			}

			if(!$uid) {
				throw new AuthentificationFailedException('no user id provided');
			}
		} catch(AuthentificationFailedException $exception) {
			if($this->session->authenticated()) {
				Analog::error(get_class($exception).': '.$exception->getMessage());
				return $this->redirect('/settings?update-oauth=failed#oauth');
			}
			throw $exception;
		}

		if($this->session->authenticated()) {
			// we ignore returnto
			unset($_SESSION['returnto']);

			// test, if other user has already used this uid
			$test_user = ModelFactory::build('User')->byOauth($provider, $uid);
			if($test_user) {
				$this->redirect('/settings?update-oauth=duplicate#oauth');
				return;
			}

			// we now trust provider and user
			$user = $this->session->getUser();
			$user->setOauth($provider, $uid);
			$user->save();

			$this->redirect('/settings?update-oauth=success#oauth');
			return;
		} else {
			// grab user with openid data fitting
			$user = ModelFactory::build('User')->byOauth($provider, $uid);

			if($user instanceof User) {
				$url = '/';
				if($returnto) {
					$url = $returnto;
				}
				$this->session->start($user, false, false);
				$this->redirect($url.'#');
				return;
			} else {
				$user = ModelFactory::build('User')->byEmail($email);
				if($user instanceof User) {
					$this->redirect('/login?unknown-oauth&returnto=/settings#');
					return;
				}

				// if no user matches register new user
				$_SESSION['register_oauth'] = array('provider' => $provider_title, 'uid' => $uid, 'username' => $nickname, 'email' => $email);
				$url = '/register';
				if($returnto) {
					$url .= '?returnto='.$returnto;
				}
				$this->redirect($url.'#');
				return;
			}
		}
	}

}
