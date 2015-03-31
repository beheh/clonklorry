<?php

namespace Lorry\Service;

use Lorry\Service;
use Lorry\Logger\LoggerFactoryInterface;
use Lorry\Email;
use Lorry\TemplateEngineInterface;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Exception;
use RuntimeException;

class MailService extends Service
{
    /**
     * @var Lorry\Service\Config
     */
    protected $config;

    /**
     * @var Lorry\Service\LocalisationService
     */
    protected $localisation;

    /**
     * @var Lorry\Service\SecurityService
     */
    protected $security;

    /**
     * @var Lorry\TemplateEngineInterface
     */
    protected $templating;

    public function __construct(LoggerFactoryInterface $loggerFactory,
        ConfigService $config, LocalisationService $localisation,
        SecurityService $security, TemplateEngineInterface $templating)
    {
        parent::__construct($loggerFactory);
        $this->config = $config;
        $this->localisation = $localisation;
        $this->security = $security;
        $this->templating = $templating;
    }
    /**
     *
     * @var \Swift_Mailer;
     */
    protected $mailer;

    protected function ensureMailer()
    {
        if ($this->mailer) {
            return true;
        }
        $transport = Swift_SmtpTransport::newInstance($this->config->get('mail/smtp-host'),
                $this->config->get('mail/smtp-port'),
                $this->config->get('mail/smtp-encryption'))
            ->setUsername($this->config->get('mail/username'))
            ->setPassword($this->config->get('mail/password'));
        $this->mailer = new Swift_Mailer($transport);
        return true;
    }

    public function build($email)
    {
        $class = '\\Lorry\\Email\\'.$email;
        if (!class_exists($class)) {
            throw new RuntimeException('unknown email "'.$class.'"');
        }
        $instance = new $class();
        $instance->setConfigService($this->config);
        $instance->setLocalisationService($this->localisation);
        $instance->setSecurityService($this->security);
        $instance->setTwig($this->templating);
        return $instance;
    }

    /**
     *
     * @param Email $email
     * @return bool
     */
    public function send(Email $email)
    {
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
        if ($replyto) {
            $message->setReplyTo($replyto);
        }

        $this->logger->info('sending "'.get_class($email).'" email to "'.$email->getRecipent().'"');

        try {
            return $this->mailer->send($message);
        } catch (Exception $ex) {
            $this->logger->error('error sending "'.get_class($email).'" email to "'.$email->getRecipent().'"');
            return false;
        }
    }
}
