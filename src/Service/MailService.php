<?php

namespace Lorry\Service;

use Analog\Analog;
use Lorry\Email;
use Lorry\EmailFactory;
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

	public function send(Email $email, $language = false) {
		$this->ensureMailer();

		$this->localisation->silentLocalize($language);
		$email->write();
		$this->localisation->resetLocalize();

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

		try {
			return $this->mailer->send($message);
		}
		catch(\Exception $exception) {
			$message = get_class($this).': '.get_class($exception).' '.$exception->getMessage().' in '.$exception->getTraceAsString();
			Analog::error($message);
		}
		return false;
	}

	public function sendActivation(\Lorry\Model\User $user, $url) {
		$activation = EmailFactory::build('Activate');
		$activation->setRecipent($user->getEmail());
		$activation->setUrl($url);
		$activation->setUsername($user->getUsername());
		return $this->send($activation, $user->getLanguage());
	}

}
