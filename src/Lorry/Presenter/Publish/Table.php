<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Table extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			$this->display('publish/table.twig');
		} else {
			$this->display('publish/greeter.twig');
		}
	}

}