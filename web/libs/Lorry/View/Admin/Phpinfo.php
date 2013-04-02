<?php

class Lorry_View_Admin_Phpinfo extends Lorry_View_Admin {

	protected function render() {
		return phpinfo();
	}

}