<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Twig_Environment;

abstract class Email
{

    /**
     *
     * @var \Lorry\Service\ConfigService
     */
    protected $config;

    public function setConfigService(ConfigService $config)
    {
        $this->config = $config;
    }

    /**
     *
     * @var \Lorry\Service\LocalisationService
     */
    protected $localisation;

    public function setLocalisationService(LocalisationService $localisation)
    {
        $this->localisation = $localisation;
    }

    /**
     *
     * @var \Lorry\Service\SecurityService
     */
    protected $security;

    public function setSecurityService(SecurityService $security)
    {
        $this->security = $security;
    }

    /**
     *
     * @var \Twig_Environment;
     */
    private $twig;

    public function setTwig(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    private $recipent;

    public function setRecipent($recipent)
    {
        $this->recipent = $recipent;
    }

    public function getRecipent()
    {
        return $this->recipent;
    }

    private $replyto;

    public function setReplyTo($replyto)
    {
        $this->replyto = $replyto;
    }

    public function getReplyTo()
    {
        return $this->replyto;
    }

    public function setUsername($username)
    {
        $this->context['username'] = $username;
    }

    protected $context = array();

    abstract protected function write();

    private $subject;

    public function getSubject()
    {
        $this->write();
        return $this->subject;
    }

    private $message;
    private $plain;

    public function getPlainMessage()
    {
        $this->plain = true;
        $this->write();
        return $this->message;
    }

    public function getMessage()
    {
        $this->plain = false;
        $this->write();
        return $this->message;
    }

    protected function render($name)
    {
        $template = $this->twig->loadTemplate('email/'.$name);
        $context = array();
        if ($this->plain) {
            $context = array('email_plain' => true);
        }
        $context = array_merge(array('brand' => $this->config->get('brand')), $context, $this->context);
        $this->subject = $template->renderBlock('subject', $context);
        $this->message = $template->render($this->context);
    }

}
