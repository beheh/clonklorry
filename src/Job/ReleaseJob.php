<?php

namespace Lorry\Job;

use Lorry\Job;
use Lorry\ModelFactory;

class ReleaseJob extends Job {

	public final static function getQueue() {
		return 'release';
	}

	public function perform() {
		$release = ModelFactory::build('Release')->byId(1);
		//$this->cdn->transfer('addon1/release1/ModernCombat.c4d');		
		$release->setShipping(false);
		$release->save();
	}

}
