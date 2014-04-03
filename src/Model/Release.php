<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ModelFactory;

class Release extends Model {

	public function __construct() {
		parent::__construct('release', array(
			'addon' => 'int',
			'version' => 'string',
			'timestamp' => 'int',
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
		return $this->order('timestamp', true)->limit(1)->byValue('addon', $addon);
	}

	public function setTimestamp($timestamp) {
		return $this->setValue('timestamp', $timestamp);
	}

	public function getTimestamp() {
		return $this->getValue('timestamp');
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

}