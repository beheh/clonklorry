<?php

namespace Lorry\Views;

use Lorry\View;

class Index extends View {

	protected function allow() {
		return true;
	}

	protected function render() {
		if((isset($_GET['greet']) && $_GET['greet'] == 1) || ((!isset($_COOKIE['lorry_greeted']) || !$_COOKIE['lorry_greeted']) && (!isset($_GET['greet']) || $_GET['greet'] == 0))) {
			if(!isset($_COOKIE['lorry_greeted']) || !$_COOKIE['lorry_greeted']) {
				setcookie('lorry_greeted', '1', time() + 60 * 60 * 24 * 365, '/');
			}
			return $this->lorry->twig->render('greeter.twig');
		} else {
			if($this->lorry->session->authenticated()) {
				$greeting = array('title' => sprintf(_('Welcome back, %s!'), $this->lorry->session->getUser()->getUsername()), 'message' => 'Nice to see you again!');
			} else {
				$greeting = array('title' => _('Hi there!'), 'message' => _('Nice to see you!'));
			}
			return $this->lorry->twig->render('store/front.twig', array('greeting' => $greeting));
		}
	}

}