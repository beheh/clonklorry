<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ModelFactory;

class Release extends Model {

	public function __construct() {
		parent::__construct('release', array(
			'addon' => 'int',
			'version' => 'string',
			'timestamp' => 'datetime',
			'assetsecret' => 'string',
			'description' => 'text'));
	}

	public final function setAddon($addon) {
		return $this->setValue('addon', $addon);
	}

	public final function byAddon($addon) {
		return $this->byValue('addon', $addon);
	}

	public final function getAddon() {
		return $this->getValue('addon');
	}

	public final function fetchAddon() {
		return ModelFactory::build('Addon')->byId($this->getAddon());
	}

	public final function byGame($game) {
		$addons = ModelFactory::build('Addon')->all()->byGame($game);
		$releases = array();
		foreach($addons as $addon) {
			$release = ModelFactory::build('Release')->latest($addon->getId());
			if($release) $releases[] = $release;
		}
		return $releases;
	}

	public final function byOwner($owner) {
		$addons = ModelFactory::build('Addon')->all()->byOwner($owner);
		$releases = array();
		foreach($addons as $addon) {
			$release = ModelFactory::build('Release')->latest($addon->getId());
			if($release) $releases[] = $release;
		}
		return $releases;
	}

	public final function setVersion($version) {
		$this->validateRegexp($version, '/^([a-zA-Z0-9-.]+)$/');
		return $this->setValue('version', $version);
	}

	public final function byVersion($version, $addon) {
		return $this->byValues(array('addon' => $addon, 'version' => $version));
	}

	public final function getVersion() {
		return $this->getValue('version');
	}

	public final function latest($addon) {
		$releases = $this->order('timestamp', true)->all()->byValue('addon', $addon);
		foreach($releases as $release) {
			if(!$release->isReleased()) {
				continue;
			}
			return $release;
		}
		return null;
	}

	public final function isReleased() {
		if($this->getTimestamp() === null) {
			return false;
		}

		if($this->getTimestamp() > time()) {
			return false;
		}
		return true;
	}

	public function setTimestamp($timestamp) {
		return $this->setValue('timestamp', $timestamp);
	}

	public function getTimestamp() {
		return $this->getValue('timestamp');
	}

	public function isScheduled() {
		return $this->getTimestamp() !== null;
	}

	public function setDescription($description) {
		return $this->setValue('description', $description);
	}

	public function getDescription() {
		return $this->getValue('description');
	}

	public function fetchRequirements() {
		return ModelFactory::build('Dependency')->all()->byRelease($this->getId());
	}

	public function fetchDependencies() {
		return ModelFactory::build('Dependency')->all()->byRequired($this->getId());
	}

	public function onSave() {
		$this->setValue('assetsecret', md5($this->getAddon().time().uniqid()));
	}

	public function getAssetSecret() {
		return $this->getValue('assetsecret');
	}

}
