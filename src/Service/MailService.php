<?php

namespace Lorry\Service;

use Twig_Environment;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class MailService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	
	/**
	 *
	 * @var \Twig_Environment
	 */
	protected $twig;

	public function setTwig(Twig_Environment $twig) {
		$this->twig = $twig;
	}
	
	/**
	 *
	 * @var \Swift_Mailer;
	 */
	protected $mailer;

	protected function ensureMailer() {
		if($this->mailer) {
			return true;
		}
		$transport = Swift_SmtpTransport::newInstance($this->config->get('mail/smtp-host'), $this->config->get('mail/smtp-port'), $this->config->get('mail/smtp-encryption'))
				->setUsername($this->config->get('mail/username'))
				->setPassword($this->config->get('mail/password'));
		$this->mailer = new Swift_Mailer($transport);
		return true;
	}
	
	public function prepare($template, $context = array()) {
		$this->ensureMailer();
		$body = $this->twig->render('email/'.$template, $context);
		$message = Swift_Message::newInstance()
				->setFrom($this->config->get('mail/from'))
				->setBody(strip_tags($body))
				->addPart($body, 'text/html');
		return $message;
	}
	
	public function send($message) {
		$this->ensureMailer();
		return $this->mailer->send($message);
	}

}
