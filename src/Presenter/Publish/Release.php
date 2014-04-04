<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\ModelValueInvalidException;

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
		if(!isset($this->context['new_version'])) {
			$this->context['new_version'] = $release->getVersion();
		}
		if(isset($_GET['version-changed'])) {
			$this->success('version', gettext('Release saved.'));
		}

		$this->display('publish/release.twig');
	}

	public function post($id, $version) {
		$this->security->requireLogin();
		$this->security->requireValidState();

		$addon = $this->getAddon($id);
		$release = $this->getRelease($addon->getId(), $version);

		if(isset($_POST['general-submit'])) {
			$new_version = filter_input(INPUT_POST, 'version');
			$this->context['new_version'] = $new_version;

			$errors = array();

			try {
				$existing = ModelFactory::build('Release')->byVersion($new_version, $id);
				if($existing && $existing->getId() != $release->getId()) {
					$errors[] = gettext('Version already exists.');
				}

				$release->setVersion($new_version);
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Version is %s.'), $ex->getMessage());
			}

			if(empty($errors)) {
				if($release->modified()) {
					if($release->save()) {
						$this->success('version', gettext('Version saved.'));
						$this->redirect('/publish/addons/'.$addon->getId().'/'.$new_version.'?version-changed');
					} else {
						$this->error('version', gettext('Error saving the release.'));
					}
				}
			} else {
				$this->error('version', implode($errors, '<br>'));
			}
		}

		return $this->get($id, $version);
	}

}
