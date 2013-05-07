<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;

class Table extends Presenter {

	public function get() {

		$this->display('addon/table.twig');
	}

}