<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;

class Overview extends Presenter {

	public function get($game, $addon, $release = 'latest') {
		$game = ModelFactory::build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException('game '.$game);
		}
		echo $addon.'-'.$release.' for '.$game;
	}

}