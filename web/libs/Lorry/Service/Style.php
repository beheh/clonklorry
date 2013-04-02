<?php

require_once ROOT.'libs/Lessphp/lessc.inc.php';

class Lorry_Service_Style extends Lorry_Service {

	public function compile() {
		$lessc = new lessc();
		$lessc->checkedCompile(ROOT.'css/lorry.less', ROOT.'css/lorry.css');
		$lessc->setFormatter('compressed');
		$lessc->checkedCompile(ROOT.'css/lorry.less', ROOT.'css/lorry.min.css');
	}

}

