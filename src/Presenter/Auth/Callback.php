<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\AuthentificationFailedException;
use Lorry\Exception\FileNotFoundException;
use LightOpenID;
use OAuth2\Client\Provider\Google;
use OAuth2\Client\Provider\Facebook;

class Callback extends Presenter {

	public function get($provider) {

		//session_start();
		//unset($_SESSION['register_oauth']);

		$oauth_provider = null;

		$uid = null;
		$nickname = null;
		$email = null;

		try {
			switch($provider) {
				case 'openid':
					$provider_title = 'OpenID';
					$openid = new LightOpenID('localhost');
					if($openid->mode == 'cancel') {
						return $this->redirect('/register');
					}
					if(!$openid->validate()) {
						throw new AuthentificationFailedException('openid validation failed');
					}
					$attributes = $openid->getAttributes();
					$uid = $openid->identity;
					$email = $attributes['contact/email'];
					break;
				case 'google':
					$provider_title = 'Google';
					$oauth_provider = new Google(array(
						'clientId' => $this->config->get('oauth/google-id'),
						'clientSecret' => $this->config->get('oauth/google-secret'),
						'redirectUri' => $this->config->get('base').'/auth/callback/google'
					));
					break;
				case 'facebook':
					$provider_title = 'Facebook';
					$oauth_provider = new Facebook(array(
						'clientId' => $this->config->get('oauth/facebook-id'),
						'clientSecret' => $this->config->get('oauth/facebook-secret'),
						'redirectUri' => $this->config->get('base').'/auth/callback/facebook'
					));
					break;
				default:
					throw new FileNotFoundException;
					break;
			}

			if($oauth_provider) {
				if(isset($_GET['error'])) {
					if($_GET['error'] == 'access_denied') {
						if($this->session->authenticated()) {
							return $this->redirect('/settings#oauth');
						} else {
							return $this->redirect('/register');
						}
					}
					throw new AuthentificationFailedException($_GET['error']);
				}

				try {
					$token = $oauth_provider->getAccessToken('authorization_code', array('code' => filter_input(INPUT_GET, 'code')));
				} catch(\Exception $ex) {
					throw new AuthentificationFailedException('could net get access token');
				}
				if(!$token) {
					echo $token;
					throw new AuthentificationFailedException('invalid code');
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
				$this->redirect('/settings?update-oauth=failed#oauth');
				return;
			}
			throw $exception;
		}

		if($this->session->authenticated()) {
			// we now trust provider and user
			$user = $this->session->getUser();
			$user->setOauth($provider, $uid);
			$user->save();

			$this->redirect('/settings?update-oauth=success#oauth');
		} else {
			// grab user with openid data fitting
			$user = ModelFactory::build('User')->byOauth($provider, $uid);

			if($user != null) {
				$this->session->start($user, true);
				$this->redirect('/#');
			} else {
				$user = ModelFactory::build('User')->byEmail($email);
				if($user != null) {
					$this->redirect('/login?unknown-oauth#');
					return;
				}

				// if no user matches register new user
				$_SESSION['register_oauth'] = array('provider' => $provider_title, 'uid' => $uid, 'username' => $nickname, 'email' => $email);
				$this->redirect('/register#');
			}
		}
	}

}
