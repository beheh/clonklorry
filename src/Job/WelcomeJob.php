<?php

namespace Lorry\Job;

class WelcomeJob extends ActivateJob {

	public function getEmail() {
		return 'Welcome';
	}

	// always perform the initial Welcome message
	public function beforePerform() {
		return;
	}
	
}
