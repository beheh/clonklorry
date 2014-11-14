<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Service\LocalisationService;

class Approve extends Presenter {

	public static function getAddon($id) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		return $addon;
	}
	
	public function get($id) {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();
	
		$addon = Approve::getAddon($id);		
		// if not submitted
		$approval_submit = $addon->getApprovalSubmit();
		if(!$approval_submit) {
			throw new ForbiddenException();
		}
		
		$game = $addon->fetchGame();
				
		$user = $addon->fetchOwner();
		
		$this->context['addon'] = $addon->getTitle();
		$this->context['user'] = $user->forPresenter();
		
		if($addon->isApproved()) {
			$this->context['approved'] = true;
			$this->context['namespace'] = $addon->getShort();
		}
		else {
			$this->context['namespace'] = $addon->getProposedShort();
		}
		$this->context['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
		$this->context['timestamp'] = date($this->localisation->getFormat(LocalisationService::FORMAT_DATETIME), $addon->getApprovalSubmit());
		
		$this->display('manage/approve.twig');
	}

}