<?php

namespace Lorry\Models;

use Lorry\Model;

class Release extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'addon' >= 'int',
			'version' => 'string'));
	}

	public final function byAddon($id) {
		return $this->byValue('addon', $id);
	}

}