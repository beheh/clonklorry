<?php

namespace Lorry\Service;

use Lorry\Environment;
use Lorry\Service;

class Twig extends Service {

	private $loader;
	private $twig;

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		$this->lorry->localisation->localize();
		$this->loader = new \Twig_Loader_Filesystem($this->lorry->getRootDir() . '../app/templates');
		$this->twig = new \Twig_Environment($this->loader, array('cache' => $this->lorry->getRootDir() . '../app/cache/twig', 'debug' => $this->lorry->config->debug == true));

		$this->twig->addExtension(new \Twig_Extension_Escaper(true));
		$this->twig->addExtension(new \Twig_Extensions_Extension_I18n());
	}

	public function addGlobal($name, $value) {
		$this->twig->addGlobal($name, $value);
	}

	public function render($name, $context = array()) {
		$this->lorry->style->compile();
		return $this->twig->render($name, $context);
	}

}

