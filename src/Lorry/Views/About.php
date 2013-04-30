<?php

namespace Lorry\Views;

use Lorry\View;

class About extends View {

	private $action;

	protected function hasWildcard($wildcard) {
		switch($wildcard) {
			case 'lorry':
			case 'clonk':
			case 'community':
				$this->action = $wildcard;
				break;
			default:
				return false;
				break;
		}
		return true;
	}

	protected function renderWildcard() {
		switch($this->action) {
			case 'lorry':
				return $this->lorry->twig->render('about/lorry.twig');
				break;
			case 'clonk':
				return $this->lorry->twig->render('about/clonk.twig');
				break;
			case 'community':
				return $this->lorry->twig->render('about/community.twig');
				break;
		}
	}

	protected function allow() {
		return true;
	}

	protected function render() {
		return $this->lorry->twig->render('about/lorry.twig');
	}

}

?>
