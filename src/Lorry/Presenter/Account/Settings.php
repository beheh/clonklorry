<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Settings extends Presenter {

	private $context = array();

	public function get() {
		$this->security->requireLogin();
		$user = $this->session->getUser();

		$this->context['username'] = $user->getUsername();
		$this->context['email'] = $user->getEmail();
		$this->context['clonkforge'] = sprintf($this->config->get('clonkforge'), $user->getClonkforge());
		$this->context['github'] = $user->getGithub();

		$this->context['language'] = $this->localisation->getDisplayLanguage();

		$this->twig->display('account/settings.twig', $this->context);
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

		$this->get();
	}

}