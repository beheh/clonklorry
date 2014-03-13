<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Create extends Presenter {

	public function get() {
		$this->context[] = 
		
		$this->display('publish/create.twig');
	}

}
