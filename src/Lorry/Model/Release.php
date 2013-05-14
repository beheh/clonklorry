<?php

namespace Lorry\Models;

use Lorry\Model;

class Release extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'addon' >= 'int',
			'version' => 'string',
			'timestamp' => 'int'));
	}

	public final function setAddon($addon) {
		return $this->setValue('addon', $addon);
	}

	public final function byAddon($id) {
		return $this->byValue('addon', $id);
	}

	public final function getAddon() {
		return $this->getValue('addon');
	}

	public final function setVersion($version) {
		return $this->setValue('version', $version);
	}

	public final function getVersion() {
		return $this->getValue('version');
	}

	public function setTimestamp($timestamp) {
		return $this->setValue('timestamp', $timestamp);
	}

	public function getTimestamp() {
		return $this->getValue('timestamp');
	}

}