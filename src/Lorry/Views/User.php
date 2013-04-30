<?php

namespace Lorry\Views;

use Lorry\View;

class User extends View {

	private $user;

	protected function hasWildcard($wildcard) {
		if(($this->user = $this->lorry->persistence->get('user')->byUsername($wildcard)) !== false) {
			return true;
		}

		return false;
	}

	protected function renderWildcard() {
		$context = array(
			'username' => $this->user->getUsername(),
			'self' => $this->lorry->session->authenticated() && $this->user->getId() == $this->lorry->session->getUser()->getId(),
			'banned' => false,
			'moderator' => $this->user->isModerator(),
			'administrator' => $this->user->isAdministrator(),
			'profiles' => array(),
			'comments' => array(),
			'extensions' => array()
		);
		if($this->user->getClonkforge()) {
			$context['profiles'][] = array(
				'platform' => gettext('Clonkforge'),
				'username' => $this->user->getUsername(),
				'url' => sprintf($this->lorry->config->clonkforge, urlencode($this->user->getClonkforge())));
		}
		if($this->user->getGithub()) {
			$context['profiles'][] = array(
				'platform' => gettext('GitHub'),
				'username' => $this->user->getGithub(),
				'url' => sprintf($this->lorry->config->github, urlencode($this->user->getGithub())));
		}
		return $this->lorry->twig->render('user/profile.twig', $context);
	}

	protected function render() {
		return $this->lorry->twig->render('user/list.twig');
	}

	protected final function allow() {
		return true;
	}

}

?>
