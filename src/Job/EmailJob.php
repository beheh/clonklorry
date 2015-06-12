<?php

namespace Lorry\Job;

use Lorry\Email\Email;

abstract class EmailJob extends AbstractJob
{

    abstract public function getEmail();

    abstract public function getRecipent();

    public function getQueue()
    {
        return 'email';
    }

    public function prepareEmail(Email $email, $args)
    {
        $email->setRecipent($this->getRecipent());
    }

    final public function execute()
    {
        $email = $this->mail->build($this->getEmail());
        $this->prepareEmail($email, $this->payload['args']);
        $this->mail->send($email);
    }
}
