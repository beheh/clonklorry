<?php

namespace Lorry\Job;

use Lorry\Job;

class CdnTransferJob extends Job {

	public final static function getQueue() {
		return 'cdn';
	}

	public function perform() {
		$this->cdn->transfer('addon1/release1/ModernCombat.c4d');
	}

}
