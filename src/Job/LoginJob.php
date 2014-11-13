<?php

namespace Lorry\Job;

class LoginJob extends UserEmailJob {
	
	public function getEmail() {
		return 'Login';
	}
}
