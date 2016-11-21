<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter\AbstractPresenter;

class Tickets extends AbstractPresenter
{

    public function get()
    {
        $this->security->requireModerator();
        $this->offerIdentification();
        $this->security->requireIdentification();

        $tickets = array();
        foreach ($this->manager->getRepository('Lorry\Model\Ticket')->getAll() as $ticket) {
            $tickets[] = $ticket->forPresenter($this->localisation->getFormat(\Lorry\Service\LocalisationService::FORMAT_DATETIME));
        }

        $this->context['tickets'] = $tickets;

        $this->display('manage/moderator/tickets.twig');
    }
}
