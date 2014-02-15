<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Table extends Presenter {

	public function get() {
		$this->offerIdentification();
		$this->security->requireModerator(); //@TODO rate limiting

		$users = ModelFactory::build('User');

		$filter = filter_input(INPUT_GET, 'filter');
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
				$users = $users->byAnything();
				break;
		}
		foreach($users as $user) {
			$this->context['users'][] = $user->getUsername();
		}


		$this->display('user/table.twig');
	}

}