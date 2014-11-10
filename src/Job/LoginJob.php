<?php

namespace Lorry\Job;

class LoginJob extends EmailJob {
	
	public function getEmail() {
		return 'Login';
	}
}
