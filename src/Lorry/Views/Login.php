<?php

namespace Lorry\Views;

use Lorry\View;

class Login extends View {

	protected function allow() {
		return true;
	}

	protected function render() {
		if($this->lorry->session->authenticated()) {
			$this->redirect($this->lorry->config->base);
		}

		$context = array('success' => false, 'username' => false, 'remember' => false, 'showmail' => false);
		$context['remember'] = $this->lorry->session->shouldRemember();
		if(isset($_COOKIE['lorry_login_email']) && $_COOKIE['lorry_greeted']) {
			$context['showmail'] = true;
		}
		if(isset($_POST['username']) && isset($_POST['password'])) {
			// standard login procedure
			$user = false;
			$user = $this->lorry->persistence->get('user')->byUsername(trim($_POST['username']));
			if($user !== false) {
				if($user->matchPassword($_POST['password'])) {
					$remember = false;
					if(isset($_POST['remember']) && $_POST['remember'] === 'yes') {
						$remember = true;
					}
					$this->lorry->session->start($user, $remember);
					$this->redirect($this->lorry->config->base);
				} else {
					$context['message'] = gettext('Password wrong.');
					$context['username'] = $_POST['username'];
				}
			} else {
				$context['message'] = _('Unknown username.');
			}
			$context['showmail'] = false;
			$context['remember'] = isset($_POST['remember']);
			setcookie('lorry_login_email', '', 0, '/'); // reset the cookie for auto-showing mail
		} else if(isset($_GET['login-email'])) {
			// alternative login via email or when user has forgotten password
			if(isset($_POST['email'])) {
				$email = trim($_POST['email']);
				$user = false;
				$user = $this->lorry->persistence->get('user')->byEmail($email);
				if($user) {
					if($this->lorry->email->send($email, 'login', array())) {
						$context['success'] = true;
						$context['message'] = sprintf(_('We have sent a one-time login to %s.'), $email);
					} else {
						$context['message'] = _('The mail could not be sent.');
					}
					setcookie('lorry_login_email', '1', time() + 60 * 60 * 24 * 365, '/');  // auto-show mail in future now for autofill
				} else {
					$context['message'] = _('Unknown email address.');
				}
			}
			$context['showmail'] = true;
		} else if(isset($_GET['register']) && $_GET['register'] == true) {
			// after registering a new account
			$context['success'] = true;
			$context['message'] = _('You have been successfully registered.');
			if(isset($_GET['username'])) {
				$context['username'] = trim($_GET['username']);
			}
		} else if(isset($_GET['confirm']) && $_GET['confirm'] == true) {
			// after activating an account
			$context['success'] = true;
			$context['message'] = _('Your account has been activated.');
			if(isset($_GET['username'])) {
				$context['username'] = trim($_GET['username']);
			}
		} else if(isset($_GET['logout']) && $_GET['logout'] == true) {
			// after logging out
			$context['success'] = true;
			$context['message'] = _('You have been logged out.');
		}
		return $this->lorry->twig->render('account/login.twig', $context);
	}

}



