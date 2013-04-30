<?php

namespace Lorry\Service;

use Lorry\Service;

class Security extends Service {

	public function hash($password) {
		$hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
		return $hash;
	}

	public function verify($password, $hash) {
		return password_verify($password, $hash) === true;
	}

	public function castSecret() {
		return base64_encode(openssl_random_pseudo_bytes(64));
	}
}