<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Overview extends Presenter {

	public function get($game, $addon, $release = 'latest') {
		$game = ModelFactory::build('Game')->byShort($game);
		if(!$game) {
			throw new FileNotFoundException('game '.$game);
		}
		echo $addon.'-'.$release.' for '.$game;
	}

}