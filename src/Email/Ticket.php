<?php

namespace Lorry\Email;

use Lorry\Email;

class Ticket extends Email
{

    public function write()
    {
        $this->setRecipent($this->config->get('contact/feedback'));
        $this->render('ticket.twig');
    }

    public function setUser($user)
    {
        $this->context['user'] = $user->forPresenter();
    }

    public function setStaff($staff)
    {
        $this->context['staff'] = $staff->forPresenter();
    }

    public function setTicketId($id)
    {
        $this->context['ticketid'] = $id;
    }

    public function setMessage($message)
    {
        $this->context['message'] = nl2br(htmlspecialchars($message));
    }
}
