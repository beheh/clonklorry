<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;
use Lorry\Model\Ticket;
use Lorry\Validator\TicketValidator;
use Lorry\Exception\ValidationException;


class Contact extends Presenter
{

    public function get()
    {
        $user = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
        }

        $this->context['user'] = $user;

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
        $ticketValidator = new TicketValidator();
        $ticketRepository = $this->manager->getRepository('Lorry\Model\Ticket');

        $user = false;
        if ($this->session->authenticated()) {
            $user = $this->session->getUser();
        }

        $ticket = new Ticket();

        if ($user) {
            $ticket->setAssociatedUser($user);
        }

        $subject = trim(filter_input(INPUT_POST, 'subject'));
        $this->context['subject'] = $subject;
        $ticket->setSubject($subject);

        $message = trim(filter_input(INPUT_POST, 'message'));
        $this->context['message'] = $message;
        $ticket->setMessage($message);

        /*if (empty($errors)) {
            $existing = $this->persistence->build('Ticket')->byHash($ticket->getHash());
            if ($existing) {
                $errors[] = gettext('This message has already been sent.');
            }
        }

        if (empty($errors)) {
            $ticket->save();
            $this->success('contact', gettext('Thank you for your message, we\'ll take a look at it.'));
            $this->context['hide_form'] = true;
        } else {
            $this->context['message'] = $message;
            $this->error('contact', implode('<br>', $errors));
        }*/

        $this->get();
    }
}
