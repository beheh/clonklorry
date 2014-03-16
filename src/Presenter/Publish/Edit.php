<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Edit extends Presenter {

	public function get() {
		$this->security->requireLogin();
		
		$this->context['title'] = 'Eke Reloaded';
		
		$this->display('publish/edit.twig');
	}
	
	public function post() {
		$this->security->requireLogin();
		
		$this->get();
	}

}
