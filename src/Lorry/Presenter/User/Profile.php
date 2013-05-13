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

		$this->context['administrator'] = $user->isAdministrator();
		$this->context['moderator'] = $user->isModerator();

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

		$addons = ModelFactory::build('Addon')->all()->byOwner($user->getId());
		$this->context['addons'] = array();
		foreach($addons as $addon) {
			$user_addon = array();
			$user_addon['title'] = $addon->getTitle();
			$game = ModelFactory::build('Game')->byId($addon->getGame());
			if($game) {
				$user_addon['url'] = $this->config->get('base').'/addons/'.$game->getShort().'/'.$addon->getShort();
			}
			$this->context['addons'][] = $user_addon;
		}


		$comments = ModelFactory::build('Comment')->all()->order('timestamp', true)->byOwner($user->getId());
		$this->context['comments'] = array();
		foreach($comments as $comment) {
			$user_comment = array();
			$user_comment['timestamp'] = date($this->localisation->getFormat(LocalisationService::FORMAT_DATETIME), $comment->getTimestamp());
			$user_comment['content'] = $comment->getContent();
			$user_comment['url'] = $this->config->get('base').'/comments/'.$comment->getId();
			$this->context['comments'][] = $user_comment;
		}

		$this->display('user/profile.twig');
	}

}