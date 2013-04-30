<?php

namespace Lorry\Service;

use Lorry\Service;

class Verify extends Service {

	const USERNAME_OK = 1;
	const USERNAME_TO_SHORT = 2;
	const USERNAME_TO_LONG = 3;
	const USERNAME_FORBIDDEN = 4;

	public static function username($username) {
		if(strlen($username) < 3) {
			return self::USERNAME_TO_SHORT;
		}
		if(strlen($username) > 16) {
			return self::USERNAME_TO_LONG;
		}
		$forbidden = array('admin', 'matthes');
		foreach($forbidden as $word) {
			if(levenshtein(strtolower($word), strtolower($username)) <= 2) {
				return self::USERNAME_FORBIDDEN;
			}
		}
		return self::USERNAME_OK;
	}

	const PASSWORD_OK = 1;
	const PASSWORD_TO_SHORT = 2;
	const PASSWORD_TO_SIMPLE = 3;

	public static function password($password, $username = '') {
		if(strlen($password) < 6) {
			return self::PASSWORD_TO_SHORT;
		}
		if(strtolower($password) == strtolower($username) ||
		  levenshtein('password', strtolower($password)) <= 2) {
			return self::PASSWORD_TO_SIMPLE;
		}
		return self::PASSWORD_OK;
	}

	const EMAIL_OK = 1;
	const EMAIL_INVALID = 2;

	public static function email($email) {
		if(strpos($email, '@') === false) {
			return self::EMAIL_INVALID;
		}
		return self::EMAIL_OK;
	}

}