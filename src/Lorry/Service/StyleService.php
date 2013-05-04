<?php

namespace Lorry\Service;

use lessc;

class StyleService {

	public function compile() {
		$lessc = new lessc();
		$lessc->checkedCompile('../app/style/lorry.less', '../web/css/lorry.css');
		$lessc->setFormatter('compressed');
		$lessc->checkedCompile('../app/style/lorry.less', '../web/css/lorry.min.css');
	}

}