<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;

use Twig_Environment;

interface EmailInterface {
	public function setConfigService(ConfigService $config);
	public function setLocalisationService(LocalisationService $localisation);
	public function setSecurityService(SecurityService $session);
	public function setSessionService(SessionService $session);
	public function setTwig(Twig_Environment $twig);
}