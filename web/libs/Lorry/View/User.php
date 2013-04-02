<?php

class Lorry_View_User extends Lorry_View {

	private $user;

	protected function hasWildcard($wildcard) {
		if(($this->user = $this->lorry->persistence->get('user')->byUsername($wildcard)) !== false) {
			return true;
		}

		return false;
	}

	protected function renderWildcard() {
		return $this->user->getUsername();
	}

	protected function render() {
		return 'Userlist here';
	}

	protected final function allow() {
		return true;
	}

}

?>
