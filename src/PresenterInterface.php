<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Lorry\Service\MailService;

use Twig_Environment;

interface PresenterInterface {
	public function setConfigService(ConfigService $config);
	public function setLocalisationService(LocalisationService $localisation);
	public function setSecurityService(SecurityService $session);
	public function setSessionService(SessionService $session);
	public function setMailService(MailService $mail);
	public function setTwig(Twig_Environment $twig);
	public function handle($method, $parameters);
}