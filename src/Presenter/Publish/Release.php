<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Release extends Presenter {

	private function getAddon($id) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if($addon->getOwner() != $this->session->getUser()->getId()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	private function getRelease($id, $version) {
		$release = ModelFactory::build('Release')->byVersion($version, $id);
		if(!$release) {
			throw new FileNotFoundException();
		}
		return $release;
	}

	public function get($id, $version) {
		$this->security->requireLogin();

		$addon = $this->getAddon($id);
		$release = $this->getRelease($addon->getId(), $version);

		$this->context['title'] = $addon->getTitle();
		$this->context['addonid'] = $addon->getId();
		$this->context['version'] = $release->getVersion();


		$this->display('publish/release.twig');
	}

}
