<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Clonk extends Presenter {

	public function get() {
		$this->session->setFlag('knows_clonk', true);
		$this->context['hide_greeter'] = true;

		if(isset($_GET['returnto'])) {
			$this->redirect(filter_input(INPUT_GET, 'returnto'));
			return;
		}

		$this->display('site/clonk.twig');
	}

}