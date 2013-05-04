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

		$this->twig->display('user/profile.twig', $context);
	}

}