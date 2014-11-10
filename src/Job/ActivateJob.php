<?php

namespace Lorry\Job;

class ActivateJob extends EmailJob {
	
	public function getEmail() {
		return 'Activate';
	}

	public function getActivationToken() {
		
	}

}
