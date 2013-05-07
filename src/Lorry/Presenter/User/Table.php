<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;

class Table extends Presenter {

	public function get() {

		$this->display('user/table.twig');
	}

}