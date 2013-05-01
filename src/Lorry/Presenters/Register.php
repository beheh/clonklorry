<?php

namespace Lorry\Presenters;

use Lorry\Presenter;
use Lorry\Service\Verify;

class Register extends Presenter {

	protected function allow() {
		return true;
	}

	protected function render() {
		if($this->lorry->session->authenticated()) {
			$this->redirect($this->lorry->config->base);
		}

		$context = array('success' => false, 'message' => false, 'username' => false, 'email' => false);
		if(isset($_POST['username'])) {
			$errors = array();

			// verify username
			$username = trim($_POST['username']);
			$username_message = _('Username');
			$context['username'] = $username;
			switch($this->lorry->verify->username($username)) {
				case Verify::USERNAME_OK:
					break;
				case Verify::USERNAME_TO_SHORT:
					$errors[] = sprintf(_('%s to short.'), $username_message);
					break;
				case Verify::USERNAME_TO_LONG:
					$errors[] = sprintf(_('%s to long.'), $username_message);
					break;
				case Verify::USERNAME_FORBIDDEN:
					$errors[] = sprintf(_('%s forbidden.'), $username_message);
					break;
				default:
					$errors[] = sprintf(_('%s invalid.'), $username_message);
					break;
			}
			if($this->lorry->persistence->get('user')->byUsername($username) !== false) {
				$errors[] = sprintf(_('%s already taken.'), _('Username'));
			}

			// verify email address
			$email = trim($_POST['email']);
			$email_message = _('Email address');
			$context['email'] = $email;
			switch($this->lorry->verify->email($email)) {
				case Verify::EMAIL_OK:
					if($this->lorry->persistence->get('user')->byEmail($email) !== false) {
						$errors[] = sprintf(_('%s has already been registered.'), $email_message);
					}
					break;
				default:
					$errors[] = sprintf(_('%s invalid.'), $email_message);
					break;
			}

			// verify password(s)
			$password = $_POST['password'];
			$password_repeat = $_POST['password-repeat'];
			if($password == $password_repeat) {
				switch($this->lorry->verify->password($password, $username)) {
					case Verify::PASSWORD_OK:
						break;
					case Verify::PASSWORD_TO_SHORT:
						$errors[] = sprintf(_('%s to short.'), _('Password'));
						break;
					case Verify::PASSWORD_TO_SIMPLE:
						$errors[] = sprintf(_('%s to simple.'), _('Password'));
						break;
					default:
						$errors[] = sprintf(_('%s invalid.'), _('Password'));
						break;
				}
			} else {
				$errors[] = gettext('Passwords don\'t match.');
			}

			// attempt to create the user
			if(empty($errors)) {
				try {
					$user = $this->lorry->persistence->get('user');
					$user->setUsername($username);
					$user->setEmail($email);
					$user->setPassword($password);
					$user->save();
					$this->redirect($this->lorry->config->base.'login?register=true');
				} catch(\Exception $ex) {
					$errors[] = gettext('Error registering.');
				}
			} else {
				foreach($errors as $error) {
					if(!empty($context['message'])) {
						$context['message'] .= '<br>';
					}
					$context['message'] .= $error;
				}
			}
		}
		return $this->lorry->twig->render('account/register.twig', $context);
	}

}

