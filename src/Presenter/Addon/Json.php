<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\OutputCompleteException;
use Lorry\ModelFactory;

class Json extends Presenter {

	public function get($name) {
		$game = ModelFactory::build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException('game '.$game);
		}

		header('Content-Type: text/json');

		$query = ModelFactory::build('Release')->all();

		$releases = $query->byGame($game->getId());
		$addons = array();
		foreach($releases as $release) {
			$addon = $release->fetchAddon();
			$entry = array(
				'id' => $addon->getShort(),
				'title' => $addon->getTitle()
			);
			if(isset($_GET['with-description'])) {
				$entry['description'] = $addon->getDescription();
			}
			if($addon->getShort()) {
				$entry['abbreviation'] = $addon->getAbbreviation();
			}
			$entry['latest'] =	$release->getVersion();
			$addons[] = $entry;
		}

		echo json_encode($addons);

		throw new OutputCompleteException();
	}

}
