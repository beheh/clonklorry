<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Settings extends Presenter {

	public function get() {
		$this->security->requireLogin();
		$user = $this->session->getUser();

		$this->context['username'] = $user->getUsername();

		$this->context['clonkforge'] = $user->getClonkforge() ? sprintf($this->config->get('clonkforge'), $user->getClonkforge()) : '';
		$this->context['github'] = $user->getGithub();

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
			$save = true;

			// Clonk Forge profile url
			$clonkforge = trim(filter_input(INPUT_POST, 'clonkforge'));
			$this->context['clonkforge'] = $clonkforge;
			if(!empty($clonkforge)) {
				// verify profile url
				$scanned = sscanf($clonkforge, $this->config->get('clonkforge'));
				if(count($scanned) == 1 && is_numeric($scanned[0]) && $scanned[0] > 0) {
					$clonkforge = $scanned[0];
				} else {
					$save = false;
					$this->error('profiles', gettext('Invalid Clonk Forge profile.'));
				}
			} else {
				// unset the profile url
				$clonkforge = null;
			}
			$user->setClonkforge($clonkforge);

			// GitHub name
			$github = trim(filter_input(INPUT_POST, 'github'));
			$this->context['github'] = $github;
			if(!empty($github)) {
				// verify username
				if(!preg_match('#^'.$this->config->get('github_name').'$#', $github)) {
					$save = false;
					$this->error('profiles', gettext('Invalid GitHub name.'));
				}
			} else {
				// unset the username
				$github = null;
			}
			$user->setGithub($github);

			if($user->modified() && $save) {
				$user->save();
				$this->success('profiles', gettext('Your linked profiles were changed.'));
			}
		}

		if(isset($_GET['change-contact'])) {
			$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
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
			$this->success('remote-logout', gettext('All other devices were logged out.'));

			$user->regenerateSecret();
			$user->save();
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