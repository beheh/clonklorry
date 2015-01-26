<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\EmailFactory;

class Ticket extends Presenter {

	public static function getTicket($id) {
		$ticket = ModelFactory::build('Ticket')->byId($id);
		if(!$ticket) {
			throw new FileNotFoundException();
		}
		return $ticket;
	}

	public function get($id) {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$ticket = $this->getTicket($id);
		$this->context['number'] = $ticket->getId();
		$this->context['request'] = $ticket->getRequest();
		$this->context['acknowledged'] = $ticket->isAcknowledged();
		$this->context['escalated'] = $ticket->isEscalated();

		$user = $ticket->fetchUser();
		if($user) {
			$this->context['user'] = $user->forPresenter();
		}
		$staff = $ticket->fetchStaff();
		if($staff) {
			$this->context['staff'] = $staff->forPresenter();
		}

		$this->display('manage/moderator/ticket.twig');
	}

	public function post($id) {
		$this->security->requireModerator();
		$this->security->requireIdentification();

		$this->security->requireValidState();

		$ticket = $this->getTicket($id);

		if(!$ticket->isEscalated() && !$ticket->isAcknowledged()) {
			if(isset($_POST['escalate'])) {
				$feedback = EmailFactory::build('Feedback');

				$user = $ticket->fetchUser();
				if($user) {
					$feedback->setReplyTo($user->getEmail());
				}

				$feedback->setSender($by);
				$feedback->setFeedback($ticket->getRequest());

				if($this->mail->send($feedback)) {
					$this->success('contact', gettext('Your message was sent. Thank you for your feedback.'));
				} else {
					$this->error('contact', gettext('Sorry, your feedback couldn\'t be sent.'));
				}
				$ticket->escalate();
			} elseif(isset($_POST['acknowledge'])) {
				$ticket->acknowledge();
				$ticket->setStaff($this->session->getUser()->getId());
			}
			$ticket->setStaff($this->session->getUser()->getId());
			if($ticket->modified()) {
				$ticket->save();
			}
		}

		$this->get($id);
	}

}
