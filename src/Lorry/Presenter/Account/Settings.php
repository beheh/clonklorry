<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\Exception\ModelValueInvalidException;

class Settings extends Presenter {

	public function get() {
		$this->security->requireLogin();

		$user = $this->session->getUser();

		$this->context['username'] = $user->getUsername();

		if(!isset($this->context['clonkforge'])) {
			$this->context['clonkforge'] = $user->getClonkforgeUrl();
		}
		if(!isset($this->context['github'])) {
			$this->context['github'] = $user->getGithub();
		}

		$this->context['clonkforge_placeholder'] = sprintf($this->config->get('clonkforge'), 0);
		$this->context['github_placeholder'] = $user->getUsername();

		$this->context['email'] = $user->getEmail();
		$this->context['language'] = $this->localisation->getDisplayLanguage();

		$this->context['password_exists'] = $user->hasPassword();

		$this->display('account/settings.twig');
	}

	public function post() {
		$this->security->requireLogin();

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
				$this->success('profiles', gettext('Your linked profiles were changed.'));
			}
		}

		if(isset($_GET['change-contact'])) {
			$errors = array();

			$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
			try {
				$user->setEmail($email);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Email address is %s.'), gettext('invalid'));
			}

			if(empty($errors)) {
				$user->save();
				$this->success('contact', gettext('Contact details have been changed.'));
			} else {
				$this->error('contact', implode('<br>', $errors));
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
					$user->setPassword($password_new);
					$user->save();
					if($has_password) {
						$this->success('password', gettext('Your password was changed.'));
					} else {
						$this->success('password', gettext('Your password was set.'));
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

			$this->session->authenticate($user);
			if($this->session->shouldRemember()) {
				$this->session->remember();
			} else {
				$this->session->forget();
			}
		}

		$this->get();
	}

}