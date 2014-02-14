<?php

namespace Lorry\Service;

use lessc;

class StyleService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	public function compile() {
		$lessc = new lessc();
		if($this->config->get('debug')) {
			$lessc->checkedCompile('../app/style/lorry.less', '../web/css/lorry.css');
		} else {
			if(file_exists('../web/css/lorry.css')) {
				unlink('../web/css/lorry.css');
			}
		}
		$lessc->setFormatter('compressed');
		$lessc->checkedCompile('../app/style/lorry.less', '../web/css/lorry.min.css');
	}

}
