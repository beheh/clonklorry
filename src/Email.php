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

    public function setUser($user)
    {
        $this->context['user'] = $user;
    }
    protected $context = array();

    abstract protected function write();
    
    private $subject;

    public function getSubject()
    {
        return $this->subject;
    }
    private $message;
    private $plain;

    public function getPlainMessage() {
        $this->plain = true;
        $this->write();
        return strip_tags($this->message);
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
        $context = array_merge(array('brand' => $this->config->get('brand'), 'email_plain' => $this->plain), $this->context);
        $this->subject = $template->renderBlock('subject', $context);
        $this->message = $template->render($context);
    }
}
