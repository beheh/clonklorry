<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;
use Lorry\Exception\ModelValueInvalidException;

class Contact extends Presenter
{

    public function get()
    {
        $user = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
        }

        $this->context['hide_greeter'] = true;
        $this->context['address'] = $this->config->get('contact/address');
        $this->context['legal_address'] = $this->config->get('contact/legal');
        if ($user) {
            $this->context['by'] = $user->getUsername();
        }

        $this->display('site/contact.twig');
    }

    public function post()
    {

        $user = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
        }

        $ticket = $this->persistence->build('Ticket');

        $errors = array();

        if ($user) {
            $ticket->setUser($user->getId());
        }

        $message = htmlspecialchars(filter_input(INPUT_POST, 'message',
                FILTER_SANITIZE_STRING));

        try {
            $ticket->setMessage($message);
        } catch (ModelValueInvalidException $e) {
            $errors[] = sprintf(gettext('Message text is %s.'), $e->getMessage());
        }

        if (empty($errors)) {
            $existing = $this->persistence->build('Ticket')->byHash($ticket->getHash());
            if ($existing) {
                $errors[] = gettext('This message has already been sent.');
            }
        }

        if (empty($errors)) {
            if ($ticket->save()) {
                $this->success('contact',
                    gettext('Thank you for your message, we\'ll take a look at it.'));
            } else {
                $this->error('contact',
                    gettext('Sorry, your message couldn\'t be saved.'));
            }
        } else {
            $this->context['message'] = $message;
            $this->error('contact', implode('<br>', $errors));
        }

        $this->get();
    }
}
