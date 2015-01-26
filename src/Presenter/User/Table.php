<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Table extends Presenter {

	public function get() {
		$this->security->requireModerator(); //@TODO rate limiting
		$this->offerIdentification();
		$this->security->requireIdentification();
		
		$users = ModelFactory::build('User');

		$filter = filter_input(INPUT_GET, 'filter');
		if($filter) {
			switch($filter) {
				case 'bans':
					$this->security->requireModerator();
					//@TODO filter bans
					break;
				case 'moderators':
					$this->security->requireAdministrator();
					//@TODO filter privileges
					break;
				default:
					throw new FileNotFoundException();
					break;
			}
		} else {
			$users = $users->limit(1)->byAnything();
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
