<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;

class Table extends Presenter {

	public function get() {

		$this->twig->display('user/table.twig');
	}

}