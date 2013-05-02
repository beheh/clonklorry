<?php

namespace Lorry;

use \Twig_Environment;

interface PresenterInterface {
	public function setConfig(Config $config);
	public function setTwig(Twig_Environment $twig);
}