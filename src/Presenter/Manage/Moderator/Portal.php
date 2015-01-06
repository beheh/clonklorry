<?php

namespace Lorry\Presenter\Manage\Moderator;

use Lorry\Presenter;
use Lorry\ModelFactory;

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
		
		$submitted = array();
		$addons = ModelFactory::build('Addon')->bySubmittedForApproval();
		foreach($addons as $addon) {
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
			$submitted[] = $result;
		}
		$this->context['submitted'] = $submitted;
		
		$this->display('manage/moderator/portal.twig');
	}

}
