<?php

class Lorry_View_Admin extends Lorry_View {

	protected function render() {
		;
	}

	protected final function allow() {
		return true;
		return $this->lorry->access->has('admin');
	}

}

