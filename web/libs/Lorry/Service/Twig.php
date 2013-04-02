<?php

require_once ROOT.'libs/Twig/Autoloader.php';

class Lorry_Service_Twig extends Lorry_Service {

	private $twig_loader_filesystem;
	private $twig;
	private $context = array();

	public function __construct(Lorry_Environment $lorry) {
		parent::__construct($lorry);
		Twig_Autoloader::register();
		$this->lorry->localisation->localize();
		$this->twig_loader_filesystem = new Twig_Loader_Filesystem(ROOT.'libs/Lorry/Template');
		$this->twig = new Twig_Environment($this->twig_loader_filesystem, array('cache' => ROOT.'cache/twig', 'debug' => true));
		$this->twig->addExtension(new Twig_Extensions_Extension_I18n());
	}

	public function setContext($context) {
		$this->context = $context;
		return true;
	}

	public function render($name, $context = array()) {
		$this->lorry->style->compile();
		$context = array_merge($context, $this->context);
		return $this->twig->render($name, $context);
	}

}

