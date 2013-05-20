<?php

namespace Lorry\Model;

use Lorry\Model;

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

	public final function setVersion($version) {
		return $this->setValue('version', $version);
	}

	public final function byVersion($version, $addon) {
		return $this->byValues(array('version' => $version, 'addon' => $addon));
	}

	public final function getVersion() {
		return $this->getValue('version');
	}

	public final function latest($addon) {
		return $this->order('timestamp', true)->byValue('addon', $addon);
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

}