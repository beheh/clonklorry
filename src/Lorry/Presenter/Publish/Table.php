<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Table extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			$this->twig->display('publish/table.twig');
		} else {
			$this->twig->display('publish/greeter.twig');
		}
	}

}