<?php

class Lorry_View_Team extends Lorry_View {

	protected function hasWildcard($wildcard) {
		if(strtolower($wildcard) == 'cmc') {
			return true;
		}
		return false;
	}

	protected function renderWildcard($wildcard) {
	}

	protected function render() {
		;
	}

	protected final function allow() {
		return true;
	}
}

?>