<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Profile extends Presenter {

	public function get($username) {
		$user = ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		$context['username'] = $user->getUsername();
		$context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

		$comments = ModelFactory::build('Comment')->all()->byOwner($user->getId());
		$context['comments'] = array();
		foreach($comments as $comment) {
			$context['comments'][] = $comment->getContent();
		}


		$context['profiles'] = array();
		if($user->getClonkforge()) {
			$context['profiles'][] = array(
				'platform' => gettext('Clonkforge'),
				'username' => $user->getUsername(),
				'url' => sprintf($this->config->get('clonkforge'), urlencode($user->getClonkforge())));
		}
		if($user->getGithub()) {
			$context['profiles'][] = array(
				'platform' => gettext('GitHub'),
				'username' => $user->getGithub(),
				'url' => sprintf($this->config->get('github'), urlencode($user->getGithub())));
		}

		$this->twig->display('user/profile.twig', $context);
	}

}