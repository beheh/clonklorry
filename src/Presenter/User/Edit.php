<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Edit extends Presenter {

	public function get($username) {
		$user = ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		$this->context['title'] = gettext('Edit user');
		$this->context['username'] = $user->getUsername();
		$this->context['email'] = $user->getEmail();
		$this->context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

		$this->context['administrator'] = $user->isAdministrator();
		$this->context['moderator'] = $user->isModerator();

		$this->context['profiles'] = array();
		if($user->getClonkforge()) {
			$this->context['clonkforge'] = array(
				'profile' => sprintf(gettext('%s on the ClonkForge'), $user->getUsername()),
				'url' => sprintf($this->config->get('clonkforge'), urlencode($user->getClonkforge())));
		}
		if($user->getGithub()) {
			$this->context['github'] = array(
				'profile' => sprintf(gettext('%s on GitHub'), $user->getGithub()),
				'url' => sprintf($this->config->get('github'), urlencode($user->getGithub())));
		}

		$this->display('user/edit.twig');
	}

}