<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter;

class Tickets extends Presenter {

	public function get() {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$tickets = array();
		foreach($this->persistence->build('Ticket')->all()->byAnything() as $ticket) {
			$tickets[] = $ticket->forPresenter($this->localisation->getFormat(\Lorry\Service\LocalisationService::FORMAT_DATETIME));
		}
		
		$this->context['tickets'] = $tickets;
		
		$this->display('manage/moderator/tickets.twig');
	}

}
