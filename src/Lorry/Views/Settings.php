<?php

namespace Lorry\Views;

use Lorry\View;
use Lorry\Service\Verify;

class Settings extends View {

	private $email;
	private $password;
	private $logout;
	private $link;

	protected function allow() {
		return $this->lorry->session->authenticated();
	}

	protected function render() {

		$user = $this->lorry->session->getUser();
		$this->email = array('success' => false, 'title' => '', 'message' => '');
		$this->logout = array('success' => false, 'title' => '', 'message' => '');
		$this->password = array('success' => false, 'title' => '', 'message' => '', 'exists' => $user->hasPassword());
		$this->link = array('success' => false, 'title' => '', 'message' => '');


		// change email
		if(isset($_GET['change-email'])) {

		}

		// remote logout
		else if(isset($_GET['remote-logout'])) {
			$remember = false; // @ TODO only re-remember if currently remembered
			$user->regenerateSecret();
			$user->save();
			$this->lorry->session->authenticate($user);
			if($remember) {
				$this->lorry->session->remember();
			} else {
				$this->lorry->session->forget();
			}
			$this->logout['success'] = true;
			$this->logout['message'] = _('All other devices have been logged out.');
		}

		// change password
		else if(isset($_GET['change-password'])) {
			$password_exists = $user->hasPassword();
			if(isset($_POST['password-current']) || !$password_exists) {
				if(isset($_POST['password-new']) && isset($_POST['password-new-confirm'])) {
					$password_new = $_POST['password-new'];
					$password_new_confirm = $_POST['password-new-confirm'];
					if($password_exists && !$user->matchPassword($_POST['password-current'])) {
						$this->password['message'] = _('Current password is incorrect.');
					} else if($password_new !== $password_new_confirm) {
						$this->password['message'] = _('New passwords don\'t match.');
					} else {
						switch($this->lorry->verify->password($password_new, $user->getUsername())) {
							case Verify::PASSWORD_OK:
								if($user->setPassword($password_new)) {
									$user->save();
									$this->password['success'] = true;
									if($password_exists) {
										$this->password['message'] = sprintf(_('%s was changed.'), _('Your password'));
									} else {
										$this->password['message'] = sprintf(_('%s was set.'), _('Your password'));
									}
									$this->password['exists'] = true;
								} else {
									if($password_exists) {
										$this->password['message'] = sprintf(_('%s could not be changed.'), _('Your password'));
									} else {
										$this->password['message'] = sprintf(_('%s could not be set.'), _('Your password'));
									}
								}
								break;
							case Verify::PASSWORD_TO_SHORT:
								$this->password['message'] = sprintf(_('%s to short.'), _('New password'));
								break;
							case Verify::PASSWORD_TO_SIMPLE:
								$this->password['message'] = sprintf(_('%s to simple.'), _('New password'));
								break;
							default:
								$this->password['message'] = sprintf(_('%s invalid.'), _('New password'));
								break;
						}
					}
				}
			}
		}

		// account linking
		else if(false) {
			if(isset($_GET['link'])) {
				$this->linkAccount();
			}
		}

		return $this->lorry->twig->render('account/settings.twig', array(
			  'user' => array(
				  'username' => $user->getUsername(),
				  'email' => $user->getEmail(),
				  'clonkforge' => $user->getClonkforge() ? 'http://clonkforge.net/user.php?usr='.$user->getClonkforge() : null,
				  'github' => $user->getGithub()),
			  'email' => $this->email,
			  'remove' => sprintf(_('Please type the phrase "%s":'), 'bejujular'),
			  'logout' => $this->logout,
			  'password' => $this->password,
			  'link' => $this->link)
		);
	}

	private function linkAccount() {
		$link = isset($_GET['link']) ? $_GET['link'] : 'none';
		$action = isset($_GET['action']) ? $_GET['action'] : 'none';
		switch($link) {
			case 'openid':
				$identityService = new Lorry_IdentityService_Openid('https://www.google.com/accounts/o8/id');
				$service = _('OpenID');
				break;
			case 'google':
				$provider = isset($_POST['openid-provider']) ? trim($_POST['openid-provider']) : false;
				if($action === 'init' && $provider === false) {
					return false;
				}
				$service = _('Google account');
				$identityService = new Lorry_IdentityService_Openid();
				break;
			case 'twitter':
				$service = _('Facebook account');
				$identityService = new Lorry_IdentityService_Twitter();
				break;
			case 'facebook':
				$service = _('Twitter account');
				$identityService = new Lorry_IdentityService_Facebook();
				break;
			default:
				return false;
				break;
		}
		switch($action) {
			case 'init':
				$identityService->redirect($this->lorry->config->baseUrl.'settings/?link='.$link.'&action=complete');
				break;
			case 'complete':
				if($this->link['success'] = $identityService->callback($this->lorry->session->getUser())) {
					$this->link['title'] = sprintf(_('%s successfully linked.'), $service);
					$this->link['message'] = _('You can now use it to log in.');
				} else {
					$this->link['title'] = sprintf(_('%s could no be linked.'), $service);
					$this->link['message'] = _('Something went wrong there.');
				}
				break;
			case 'remove':
				if($this->link['success'] = $identityService->remove($this->lorry->session->getUser())) {
					$this->link['title'] = sprintf(_('%s link removed.'), $service);
					$this->link['message'] = _('You can relink at any time.');
				} else {
					$this->link['title'] = sprintf(_('%s link could not be removed.'), $service);
					$this->link['message'] = _('You have not specified any other method of logging in.');
				}
				break;
			default:
				return false;
				break;
		}
		return true;
	}

	/* private function attemptOpenID() {
	  $action = $_GET['openid'];
	  if($action == 'init' || $action == 'init-google') {
	  $url = parse_url($this->lorry->config->baseUrl);
	  $openid->realm = $url['scheme'].'://'.$url['host'];
	  $openid = new LightOpenID($url['host']);
	  if($action == 'init') {
	  if(isset($_POST['openid-url']) && !empty($_POST['openid-url'])) {
	  $openid->returnUrl = $this->lorry->config->baseUrl.'settings/?openid=complete';
	  $openid->identity = $_POST['openid-url'];
	  return $this->redirect($openid->authUrl());
	  }
	  } else if($action == 'init-google') {
	  $openid->returnUrl = $this->lorry->config->baseUrl.'settings/?openid=complete-google';
	  $openid->identity = 'https://www.google.com/accounts/o8/id';
	  return $this->redirect($openid->authUrl());
	  }
	  }
	  if($action == 'complete' || $action == 'complete-google') {
	  $url = parse_url($this->lorry->config->baseUrl);
	  $openid = new LightOpenID($url['host']);
	  if($openid->mode == 'cancel') {
	  return;
	  }
	  if($openid->validate()) {
	  $this->link['success'] = true;
	  $this->link['identity'] = $openid->identity;
	  if($action == 'complete') {
	  $this->link['title'] = _('OpenID successfully linked.');
	  } else if($action == 'complete-google') {
	  $this->link['title'] = _('Google account successfully linked.');
	  }
	  $this->link['message'] = _('You can now use it to login.');
	  } else {
	  if($action == 'complete') {
	  $this->link['message'] = _('Authentication with your OpenID Provider failed.');
	  } else if($action == 'complete-google') {
	  $this->link['message'] = _('Authentication with Google failed.');
	  }
	  return;
	  }
	  }
	  }

	  private function attemptTwitter() {
	  $action = $_GET['twitter'];
	  if($action == 'init') {
	  $this->lorry->twitter->redirect($this->lorry->config->baseUrl.'settings/?twitter=complete');
	  }
	  if($action == 'complete') {
	  try {
	  $result = $this->lorry->twitter->callback();
	  if($result) {
	  $this->link['success'] = true;
	  $this->link['title'] = _('Twitter successfully linked.');
	  $this->link['message'] = _('You can now use it to login.');
	  $this->link['identity'] = $result->id;
	  } else {

	  }
	  } catch(Exception $ex) {
	  $this->link['message'] = _('Authentication with Twitter failed.');
	  }
	  }
	  }

	  private function attemptFacebook() {
	  $action = $_GET['facebook'];
	  if($action == 'init') {

	  }
	  if($action == 'complete') {

	  if(false) {
	  $this->facebook['success'] = true;
	  $this->facebook['title'] = _('Facebook successfully linked.');
	  $this->facebook['message'] = _('You can now use it to login.');
	  $this->facebook['identity'] = 0;
	  ;
	  } else {

	  }
	  }
	  } */
}

