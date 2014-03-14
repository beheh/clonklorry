<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\Exception\ModelValueInvalidException;

class Settings extends Presenter {

	public function get() {
		$this->security->requireLogin();

		if(isset($_GET['oauth'])) {
			return $this->redirect($this->session->handleOauth());
		}

		$user = $this->session->getUser();

		if(isset($_GET['update-oauth'])) {
			switch(filter_input(INPUT_GET, 'update-oauth')) {
				case 'success':
					$this->success('oauth', gettext('Connected with login service.'));
					break;
				case 'duplicate':
					$this->error('oauth', gettext('Login service is already linked to another account.'));
					break;
				case 'failed':
					$this->error('oauth', gettext('Authentification with login service failed.'));
					break;
			}
		}

		if(isset($_GET['remove-oauth'])) {
			$this->security->requireValidState();

			try {
				$user->setOauth(filter_input(INPUT_GET, 'remove-oauth'), null);
				$this->success('oauth', gettext('Removed login service.'));
				$user->save();
			} catch(ModelValueInvalidException $ex) {
				$this->error('oauth', gettext('Can\'t remove last login service.'));
			}
		}

		$this->context['username'] = $user->getUsername();

		if(!isset($this->context['clonkforge'])) {
			$this->context['clonkforge'] = $user->getClonkforgeUrl();
		}
		if(!isset($this->context['github'])) {
			$this->context['github'] = $user->getGithub();
		}

		$this->context['clonkforge_placeholder'] = sprintf($this->config->get('clonkforge'), 0);
		$this->context['github_placeholder'] = $user->getUsername();

		if(!isset($this->context['email'])) {
			$this->context['email'] = $user->getEmail();
		}
		$this->context['activated'] = $user->isActivated();

		$this->context['language'] = $this->localisation->getDisplayLanguage();

		$this->context['password_exists'] = $user->hasPassword();
		if(isset($_GET['add-password']) && !$user->hasPassword()) {
			$this->context['focus_password_new'] = true;
		}

		$oauth = array('openid', 'google', 'facebook');
		$this->context['oauth'] = array();
		foreach($oauth as $provider) {
			$this->context['oauth'][$provider] = $user->hasOauth($provider);
		}

		$this->display('account/settings.twig');
	}

	public function post() {
		$this->security->requireLogin();
		$this->security->requireValidState();

		$user = $this->session->getUser();

		if(isset($_GET['change-profiles'])) {
			$error = false;

			// Clonk Forge profile url
			$clonkforge = trim(filter_input(INPUT_POST, 'clonkforge'));
			$this->context['clonkforge'] = $clonkforge;
			try {
				$user->setClonkforgeUrl($clonkforge);
			} catch(ModelValueInvalidException $e) {
				$this->error('profiles', sprintf(gettext('Clonk Forge profile url is %s.'), gettext('invalid')));
				$error = true;
			}

			// GitHub name
			$github = trim(filter_input(INPUT_POST, 'github'));
			$this->context['github'] = $github;
			try {
				$user->setGithub($github);
			} catch(ModelValueInvalidException $e) {
				$this->error('profiles', sprintf(gettext('GitHub name is %s.'), gettext('invalid')));
				$error = true;
			}

			if($user->modified() && !$error) {
				$user->save();
				$this->success('profiles', gettext('Your links were saved.'));
			}
		}

		if(isset($_GET['change-contact'])) {
			$errors = array();

			$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
			$this->context['email'] = $email;

			try {
				$user->setEmail($email);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Email address is %s.'), gettext('invalid'));
			}

			if($user->modified()) {
				if(empty($errors)) {
					$user->save();

					if($this->mail->sendActivation($user, $this->config->get('base').'/activate')) {
						$this->warning('contact', gettext('Contact details were changed. We sent you an email for you to confirm the new address.'));
					} else {
						$this->warning('contact', gettext('Contact details were changed, but we couldn\'t send you an email to confirm. Try resending one later.'));
					}
				} else {
					$this->error('contact', implode('<br>', $errors));
				}
			} else if(isset($_POST['resend'])) {
				if($this->mail->sendActivation($user, $this->config->get('base').'/activate')) {
					$this->success('contact', gettext('Confirmation email sent.'));
				} else {
					$this->alert('contact', gettext('We can\'t send you a confirmation email right now. Try again later.'));
				}
			}
		}

		if(isset($_GET['change-language'])) {
			$language = filter_input(INPUT_POST, 'language');
			if($this->localisation->setDisplayLanguage($language)) {
				$user->setLanguage($language);
				$user->save();
				$this->redirect('/settings');
				return;
			}
		}

		if(isset($_GET['change-password'])) {
			$has_password = $user->hasPassword();
			$password_old = filter_input(INPUT_POST, 'password-old');
			$password_new = filter_input(INPUT_POST, 'password-new');
			$password_confirm = filter_input(INPUT_POST, 'password-confirm');
			if(!$has_password || $user->matchPassword($password_old)) {
				if($password_new === $password_confirm) {
					try {
						$user->setPassword($password_new);
						$user->save();
						$this->session->identify();
						if($has_password) {
							$this->success('password', gettext('Your password was changed.'));
						} else {
							$this->success('password', gettext('Your password was set.'));
						}
					} catch(ModelValueInvalidException $ex) {
						$this->error('password', sprintf(gettext('Password is %s.'), $ex->getMessage()));
					}
				} else {
					$this->context['focus_password'] = true;
					$this->error('password', gettext('Passwords do not match.'));
				}
			} else {
				$this->context['focus_password'] = true;
				$this->error('password', gettext('Password wrong.'));
			}
		}

		if(isset($_GET['remote-logout'])) {
			$user->regenerateSecret();
			$user->save();

			$this->success('remote-logout', gettext('All other devices were logged out.'));

			$this->session->refresh($user);
			if($this->session->shouldRemember()) {
				$this->session->remember();
			} else {
				$this->session->forget();
			}
		}

		$this->get();
	}

}
