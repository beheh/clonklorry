<?php

namespace Lorry\Service;

use Lorry\Service;

class Style extends Service {

	public function compile() {
		$lessc = new \lessc();
		$root = $this->lorry->getRootDir();
		$lessc->checkedCompile($root.'../app/style/lorry.less', $root.'../web/css/lorry.css');
		$lessc->setFormatter('compressed');
		$lessc->checkedCompile($root.'../app/style/lorry.less', $root.'../web/css/lorry.min.css');
	}

}