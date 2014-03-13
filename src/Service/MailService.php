<?php

namespace Lorry\Service;

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

	public function send(Email $email) {
		$this->ensureMailer();
		
		$email->write();
		
		$body = $email->getMessage();
		
		$message = Swift_Message::newInstance()
				->setFrom($this->config->get('mail/from'))
				->setTo($email->getRecipent())
				->setSubject($email->getSubject())
				->setBody(strip_tags($body))
				->addPart($body, 'text/html');
		
		$replyto = $email->getReplyTo();
		if($replyto) {
			$message->setReplyTo($replyto);
		}
		
		$this->mailer->send($message);
	}

	public function sendActivation(\Lorry\Model\User $user, $url) {
		$activation = EmailFactory::build('Activate');
		$activation->setRecipent($user->getEmail());
		$activation->setUrl($url);
		$this->send($activation);
	}
}
