<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Settings extends Presenter {

	public function get() {
		$this->security->requireLogin();
		$user = $this->session->getUser();

		$this->context['username'] = $user->getUsername();
		$this->context['clonkforge'] = sprintf($this->config->get('clonkforge'), $user->getClonkforge());
		$this->context['github'] = $user->getGithub();


		$this->context['email'] = $user->getEmail();
		$this->context['language'] = $this->localisation->getDisplayLanguage();

		$this->context['password_exists'] = $user->hasPassword();

		$this->display('account/settings.twig');
	}

	public function post() {
		$this->security->requireLogin();
		$user = $this->session->getUser();

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
						$this->success('password', gettext('Password was changed.'));
					} else {
						$this->success('password', gettext('Password was set.'));
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

		$this->get();
	}

}