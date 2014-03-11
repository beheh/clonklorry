<?php

namespace Lorry\Service;

use Swift_Mailer;
use Swift_Message;
use Swift_MailTransport;

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
	 * @var \Swift_Mailer;
	 */
	protected $mailer;

	protected function ensureMailer() {
		if($this->mailer) {
			return true;
		}
		$this->mailer = new Swift_Mailer(Swift_MailTransport::newInstance());
		return true;
	}

	public function create() {
		$this->ensureMailer();
		return Swift_Message::newInstance();
	}
	
	public function send(Swift_Message $message) {
		return $this->mailer->send($message);
	}

}
