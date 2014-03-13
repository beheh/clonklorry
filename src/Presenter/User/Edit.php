<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ModelValueInvalidException;

class Edit extends Presenter {

	public function get($username) {
		$this->offerIdentification();
		$this->security->requireModerator();

		$user = ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		if($user->isModerator() || $user->isAdministrator()) {
			$this->security->requireAdministrator();
		}

		$this->context['title'] = gettext('Edit user');

		$this->context['username'] = $user->getUsername();
		if(!isset($this->context['username_edit'])) {
			$this->context['username_edit'] = $user->getUsername();
		}

		$this->context['email'] = isset($_POST['email']) ? filter_input(INPUT_POST, 'email') : $user->getEmail();

		if(!isset($this->context['require_activation'])) {
			$this->context['require_activation'] = true;
		}

		$this->context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

		$this->context['administrator'] = $user->isAdministrator();
		$this->context['moderator'] = $user->isModerator();

		$this->context['profiles'] = array();
		if($user->getClonkforge()) {
			$this->context['clonkforge'] = array(
				'profile' => sprintf(gettext('%s on the ClonkForge'), $user->getUsername()),
				'url' => sprintf($this->config->get('clonkforge'), urlencode($user->getClonkforge())));
		}
		if($user->getGithub()) {
			$this->context['github'] = array(
				'profile' => sprintf(gettext('%s on GitHub'), $user->getGithub()),
				'url' => sprintf($this->config->get('github'), urlencode($user->getGithub())));
		}

		$this->display('user/edit.twig');
	}

	public function post($username) {
		$this->offerIdentification();
		$this->security->requireModerator();
		$this->security->requireValidState();

		$user = ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		if($user->isModerator() || $user->isAdministrator()) {
			$this->security->requireAdministrator();
		}

		$new_username = trim(filter_input(INPUT_POST, 'username'));

		if(isset($_GET['change-username']) && $username != $new_username) {
			$this->context['username_edit'] = $new_username;

			if(ModelFactory::build('User')->byUsername($new_username)) {
				$errors[] = gettext('Username already taken.');
			} else {
				try {
					$user->setUsername($new_username);
				} catch(ModelValueInvalidException $e) {
					$errors[] = sprintf(gettext('Username is %s.'), $e->getMessage());
				}
			}

			if(empty($errors)) {
				if($user->modified() && $user->save()) {
					$this->redirect('/users/'.$new_username.'/edit?change-username');
					return;
				} else {
					$this->error(gettext('Username could not be changed.'));
				}
			} else {
				$this->error('username', implode('<br>', $errors));
			}
		}

		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

		if(isset($_GET['change-contact']) && $email != $user->getEmail()) {
			
			$this->context['email'] = $email;

			$require_activation = filter_input(INPUT_POST, 'require-activation', FILTER_VALIDATE_BOOLEAN) ? true : false;
			$this->context['require_activation'] = $require_activation;

			$errors = array();

			if(ModelFactory::build('User')->byEmail($email)) {
				$errors[] = gettext('Email address already used.');
			} else {
				try {
					$user->setEmail($email);
				} catch(ModelValueInvalidException $e) {
					$errors[] = sprintf(gettext('Email address is %s.'), gettext('invalid'));
				}
			}

			if($user->modified() && empty($errors)) {

				if(!$require_activation) {
					$user->activate();
				}

				$user->save();

				if($require_activation) {
					if($this->mail->sendActivation($user, $this->config->get('base').'/activate')) {
						$this->warning('contact', gettext('Contact details were changed. We sent an email to the user to confirm this new address.'));
					} else {
						$this->warning('contact', gettext('Contact details were changed, but we couldn\'t send the user an email.'));
					}
				} else {
					$this->success('contact', gettext('Contact details were changed.'));
				}
			} else {
				$this->error('contact', implode('<br>', $errors));
			}
		}

		$this->get($username);
	}

}
