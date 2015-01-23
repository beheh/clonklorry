<?php

namespace Lorry\Job;

class ActivateJob extends UserEmailJob {
	
	public function getEmail() {
		return 'Activate';
	}
	
	public function beforePerform() {
		if(isset($this->args['address']) && $this->args['address'] != $this->getRecipent()) {
			// user has since changed his address, no need to execute
			throw new \Resque_Job_DontPerform;
		}
	}

	public function getActivationToken() {
		
	}

}
