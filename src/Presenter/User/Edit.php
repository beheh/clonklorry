<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ModelValueInvalidException;

class Edit extends Presenter {

	public function get($username) {
		$this->security->requireModerator();

		$user = ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		if($user->isModerator() || $user->isAdministrator()) {
			$this->security->requireAdministrator();
		}

		$this->context['title'] = gettext('Edit user');

		$new_username = filter_input(INPUT_POST, 'username');
		$this->context['username'] = $new_username ? $new_username : $user->getUsername();
		$this->context['email'] = $user->getEmail();
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
			if($user->save()) {
				$this->redirect('/users/'.$new_username.'/edit/');
				return;
			} else {
				$this->error(gettext('Username could not be changed.'));
			}
		} else {
			$this->error('username', implode('<br>', $errors));
		}

		$this->get($username);
	}

}
