<?php

namespace Lorry;

use \Lorry\Service\ConfigService;
use \Twig_Environment;

interface PresenterInterface {
	public function setConfig(ConfigService $config);
	public function setTwig(Twig_Environment $twig);
}