<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Service\LocalisationService;

class Profile extends Presenter {

	public function get($username) {
		$user = ModelFactory::build('User')->byUsername($username);
		if(!$user) {
			throw new FileNotFoundException('user '.$username);
		}

		$this->context['title'] = $user->getUsername();
		$this->context['username'] = $user->getUsername();
		$this->context['self'] = $this->session->authenticated() && $user->getId() == $this->session->getUser()->getId();

		$this->context['administrator'] = $user->getId() == 1; //@TODO
		$this->context['moderator'] = false;

		$comments = ModelFactory::build('Comment')->all()->order('timestamp', true)->byOwner($user->getId());
		$this->context['comments'] = array();
		foreach($comments as $comment) {
			$usercomment = array();
			$usercomment['timestamp'] = date($this->localisation->getFormat(LocalisationService::FORMAT_DATETIME), $comment->getTimestamp());
			$usercomment['content'] = $comment->getContent();
			$usercomment['url'] = $this->config->get('base');
			$this->context['comments'][] = $usercomment;
		}

		$this->context['profiles'] = array();
		if($user->getClonkforge()) {
			$this->context['profiles'][] = array(
				'platform' => gettext('Clonk Forge'),
				'username' => $user->getUsername(),
				'url' => sprintf($this->config->get('clonkforge'), urlencode($user->getClonkforge())));
		}
		if($user->getGithub()) {
			$this->context['profiles'][] = array(
				'platform' => gettext('GitHub'),
				'username' => $user->getGithub(),
				'url' => sprintf($this->config->get('github'), urlencode($user->getGithub())));
		}

		$this->display('user/profile.twig');
	}

}