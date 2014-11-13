<?php

namespace Lorry\Job;

class ActivateJob extends UserEmailJob {
	
	public function getEmail() {
		return 'Activate';
	}
	
	public function beforePerform() {
		if($this->user->getEmail() !== $this->recipent) {
			// user has since changed his address, no need to execute
			throw new \Resque_Job_DontPerform;
		}
	}

	public function getActivationToken() {
		
	}

}
