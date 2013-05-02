<?php

namespace Lorry;

class Presenter implements PresenterInterface {

	protected $config;

	public function setConfig($config) {
		$this->config = $config;
	}

	protected $twig;

	public function setTwig($twig) {
		$this->twig = $twig;
	}

}