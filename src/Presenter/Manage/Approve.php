<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

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
		$game = $addon->fetchGame();
				
		$user = $addon->fetchOwner();
		
		$this->context['addon'] = $addon->getTitle();
		$this->context['user'] = $user->forApi();
		
		$this->context['namespace'] = $addon->getShort();		
		$this->context['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
		
		$this->display('manage/approve.twig');
	}

}