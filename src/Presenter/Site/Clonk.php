<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Clonk extends Presenter {

	public function get() {
		$this->session->setFlag('knows_clonk');
		$this->context['hide_greeter'] = true;
		$this->display('site/clonk.twig');
	}

}