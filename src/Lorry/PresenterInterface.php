<?php

namespace Lorry;

use \Lorry\Service\ConfigService;
use \Lorry\Service\SessionService;
use \Twig_Environment;

interface PresenterInterface {
	public function setConfig(ConfigService $config);
	public function setSession(SessionService $session);
	public function setTwig(Twig_Environment $twig);
}