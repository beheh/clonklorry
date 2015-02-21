<?php

namespace Lorry\Service;

use Lorry\Email;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Analog\Analog;
use Exception;

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
	 * @var \Lorry\Service\LocalisationService
	 */
	protected $localisation;

	public function setLocalisationService(LocalisationService $localisation) {
		$this->localisation = $localisation;
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

	/**
	 * 
	 * @param Email $email
	 * @return bool
	 */
	public function send(Email $email) {
		$this->ensureMailer();

		$email->write();

		$body = $email->getMessage();

		$message = Swift_Message::newInstance()
				->setFrom(array($this->config->get('mail/from') => $this->config->get('brand')))
				->setTo($email->getRecipent())
				->setSubject($email->getSubject())
				->setBody(strip_tags($body))
				->addPart($body, 'text/html');

		$replyto = $email->getReplyTo();
		if($replyto) {
			$message->setReplyTo($replyto);
		}

		Analog::debug('sending mail with content: '.$body);

		try {
			return $this->mailer->send($message);
		}
		catch(Exception $ex) {
			Analog::error('error sending mail: '.$ex->getMessage());
			return false;
		}
	}

}
