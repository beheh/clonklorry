<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Game extends Presenter {

	public function get($game) {
		$this->redirect('/publish?for='.$game);
	}
}