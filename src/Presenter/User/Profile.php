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

		if($user->getRegistration()) {
			$this->context['registration'] = date($this->localisation->getFormat(LocalisationService::FORMAT_DATE), $user->getRegistration());
		}

		$this->context['profiles'] = array();
		if($user->getClonkforge()) {
			$this->context['clonkforge'] = array(
				'profile' => sprintf(gettext('%s on the ClonkForge'), $user->getUsername()),
				'url' => sprintf($this->config->get('clonkforge'), urlencode($user->getClonkforge())));
		}
		if($user->getGithub()) {
			$this->context['github'] = array(
				'profile' => sprintf(gettext('%s on GitHub'), $user->getGithub()),
				'url' => sprintf($this->config->get('github/url'), urlencode($user->getGithub())));
		}

		$games = ModelFactory::build('Game')->byAnything();

		$addons = ModelFactory::build('Addon')->all()->byOwner($user->getId());
		$this->context['addons'] = array();
		foreach($addons as $addon) {
			$user_addon = array(
				'title' => $addon->getTitle(),
				'short' => $addon->getShort(),
				'description' => $addon->getDescription()
			);
			
			$game = $addon->fetchGame();
			if($game) {
				$user_addon['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
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
