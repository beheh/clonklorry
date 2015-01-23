<?php

namespace Lorry\Job;

class LoginByEmailJob extends UserEmailJob {
	
	public function getEmail() {
		return 'Login';
	}
}
