<?php

class Lorry_View_Login extends Lorry_View {

	protected function render() {
		if($this->lorry->session->authenticated()) {
			$this->redirect($this->lorry->config->baseUrl);
		}
		$context = array();
		if(isset($_POST['username']) && isset($_POST['password'])) {
			$user = false;
			try {
				$user = $this->lorry->persistence->get('user')->byUsername($_POST['username']);
				if($user !== false) {
					if($user->matchPassword($_POST['password'])) {
						$remember = false;
						if(isset($_POST['remember']) && $_POST['remember'] === 'yes') {
							$remember = true;
						}
						$this->lorry->session->start($user, $remember);
						$this->redirect($this->lorry->config->baseUrl);
					} else {
						$context['message'] = gettext('Password wrong.');
						$context['username'] = $_POST['username'];
					}
				} else {
					$context['message'] = gettext('Unknown username.');
				}
			} catch(Exception $ex) {
				$context['message'] = gettext('Error logging in.');
			}
		}
		return $this->lorry->twig->render('login.twig', array('login' => $context));
	}

	protected function allow() {
		return true;
	}

}

