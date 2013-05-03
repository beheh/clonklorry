<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;

use Twig_Environment;

interface PresenterInterface {
	public function setConfigService(ConfigService $config);
	public function setSecurityService(SecurityService $session);
	public function setSessionService(SessionService $session);
	public function setTwig(Twig_Environment $twig);
}