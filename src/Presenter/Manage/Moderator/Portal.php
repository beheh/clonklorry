<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Service\LocalisationService;

class Portal extends Presenter {

	public function get() {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$game_objects = ModelFactory::build('Game')->byAnything();
		$games = array();
		foreach($game_objects as $game) {
			$games[$game->getId()] = $game->forPresenter();
		}
		$this->context['games'] = $games;
		
		$addons = array();
		foreach(ModelFactory::build('Addon')->bySubmittedForApproval() as $addon) {
			$result = array(
				'addon' => 
					array('id' => $addon->getId(),
						'title' => $addon->getTitle(),
						'game' => $addon->getGame()
					),
				'namespace' => $addon->getProposedShort()
				);
			$owner = $addon->fetchOwner();
			if($owner) {
				$result['user'] = $owner->forPresenter();
			}
			$addons[] = $result;
		}
		$this->context['addons'] = $addons;
		
		$tickets = array();
		foreach(ModelFactory::build('Ticket')->byNew() as $ticket) {
			$result = array(
				'id' => $ticket->getId(),
				'submitted' => date($this->localisation->getFormat(LocalisationService::FORMAT_DATETIME), $ticket->getSubmitted()),
				'message' => $ticket->getMessage()
			);
			$user = $ticket->fetchUser();
			if($user) {
				$result['user'] = $user->forPresenter();
			}
			$tickets[] = $result;
		}
		
		$this->context['tickets'] = $tickets;
		
		$this->display('manage/moderator/portal.twig');
	}

}
