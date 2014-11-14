<?php

namespace Lorry\Presenter\Debug;

use Lorry\Presenter;
use Analog;

class JobSubmitter extends Presenter {

	public function get() {
		$job = filter_input(INPUT_GET, 'job');
		Analog::info('Cleared Twig cache');
		$this->job->submit($job, array());

		echo 'Job submitted.';
	}

}
