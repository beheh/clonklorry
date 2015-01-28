<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Table extends Presenter {

	public function get() {
		$this->security->requireLogin();
		
		$users = ModelFactory::build('User');

		$filter = filter_input(INPUT_GET, 'filter');
		if($filter) {
			switch($filter) {
				case 'moderators':
					$this->security->requireAdministrator();
					$this->offerIdentification();
					$this->security->requireIdentification();
					break;
				default:
					throw new FileNotFoundException();
					break;
			}
		} else {
			$users = $users->all()->byAnything();
		}
		foreach($users as $user) {
			$this->context['users'][] = array(
				'name' => $user->getUsername(),
				'administrator' => $user->isAdministrator(),
				'moderator' => $user->isModerator()
					);
		}


		$this->display('user/table.twig');
	}

}
