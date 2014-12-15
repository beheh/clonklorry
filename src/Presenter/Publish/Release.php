<?php

namespace Lorry\Presenter\Publish;

use DateTime;
use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ModelValueInvalidException;

class Release extends Presenter {

	public static function getRelease($id, $version) {
		$release = ModelFactory::build('Release')->byVersion($version, $id);
		if(!$release) {
			throw new FileNotFoundException();
		}
		return $release;
	}

	public function get($id, $version) {
		$this->security->requireLogin();

		$addon = Edit::getAddon($id, $this->session->getUser());
		$release = Release::getRelease($addon->getId(), $version);

		if($addon->isApproved()) {
			$this->context['approved'] = true;
		} else if($addon->isSubmittedForApproval()) {
			$this->context['submitted'] = true;
		}
		$this->context['released'] = $release->isReleased();

		$this->context['title'] = sprintf(gettext('Edit %s'), $addon->getTitle().' '.$release->getVersion());
		$this->context['game'] = $addon->fetchGame()->getShort();
		$this->context['addontitle'] = $addon->getTitle();
		$this->context['addonid'] = $addon->getId();
		$this->context['version'] = $release->getVersion();

		$latest = ModelFactory::build('Release')->latest($addon->getId());
		$this->context['latest'] = ($latest && $latest->getId() == $release->getId());
		$this->context['scheduled'] = $release->isScheduled();

		/* Basic */

		if(!isset($this->context['new_version'])) {
			$this->context['new_version'] = $release->getVersion();
		}
		if(isset($_GET['version-changed'])) {
			$this->success('version', gettext('Release saved.'));
		}

		/* Files */

		/* Depedencies */

		/* Changes */

		$this->context['whatsnew'] = $release->getWhatsnew();
		$this->context['changelog'] = $release->getChangelog();

		/* Publish */

		$datetime = new DateTime('tomorrow noon');
		$this->context['datetime'] = $datetime->format('Y-m-d\TH:i:s');
		$this->context['shipping'] = false;

		$this->display('publish/release.twig');
	}

	public function post($id, $version) {
		$this->security->requireLogin();
		$this->security->requireValidState();

		$addon = Edit::getAddon($id, $this->session->getUser());
		$release = Release::getRelease($addon->getId(), $version);

		/* Basic */

		if(isset($_POST['basic-form'])) {
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
						$this->redirect('/publish/'.$addon->getId().'/'.$new_version.'?version-changed');
					} else {
						$this->error('version', gettext('Error saving the release.'));
					}
				}
			} else {
				$this->error('version', implode($errors, '<br>'));
			}
		}

		/* Files */

		/* Depedencies */

		/* Changes */

		if(isset($_POST['changes-form'])) {
			$whatsnew = trim(filter_input(INPUT_POST, 'whatsnew'));
			$changelog = trim(filter_input(INPUT_POST, 'changelog'));

			$errors = array();

			try {
				$release->setWhatsnew($whatsnew);
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('"%s" is %s.'), gettext('What\'s new?'), $ex->getMessage());
			}
			try {
				$release->setChangelog($changelog);
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Changelog is %s.'), $ex->getMessage());
			}

			if(empty($errors)) {
				if($release->modified()) {
					if($release->save()) {
						$this->success('changes', gettext('Changes saved.'));
					} else {
						$this->error('changes', gettext('Error saving the changes.'));
					}
				}
			} else {
				$this->error('changes', implode($errors, '<br>'));
			}
		}

		/* Publish */

		return $this->get($id, $version);
	}

}
