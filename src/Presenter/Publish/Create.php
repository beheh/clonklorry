<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Create extends Presenter {

	public function get() {
		if(!$this->session->authenticated()) {
			return $this->redirect('/publish');
		}
		
		$this->display('publish/create.twig');
	}

}
