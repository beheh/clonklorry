<?php

namespace Lorry\Job;

use Lorry\Job;

class ReleaseJob extends Job {

	public final static function getQueue() {
		return 'release';
	}

	public function perform() {
		
		$this->cdn->transfer('addon1/release1/ModernCombat.c4d');
	}

}
