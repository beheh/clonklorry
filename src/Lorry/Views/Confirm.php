<?php

namespace Lorry\Views;

use Lorry\View;

class Confirm extends View {

	protected function allow() {
		return true;
	}

	protected function render() {
		if(false) {
			if($this->lorry->session->authenticated()) {
				return $this->redirect($this->lorry->config->base.'settings?confirm-email&success=true');
			} else {
				return $this->redirect($this->lorry->config->base.'login?confirm=true&username=B_E');
			}
		} else {
			if($this->lorry->session->authenticated()) {
				// already done?
				return $this->redirect($this->lorry->config->base.'settings?confirm-email');
			} else {
				return $this->lorry->twig->render('error.twig', array('title' => _('Confirmation code invalid'), 'message' => _('Your email address could not be confirmed. You have either already been confirmed or followed an invalid link.')));
			}
		}
	}

}