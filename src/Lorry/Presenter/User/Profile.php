<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;

class Profile extends Presenter {

	public function get($username) {
		$user = \Lorry\ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		$this->twig->display('user/profile.twig');
	}

}