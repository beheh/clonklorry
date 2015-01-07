<?php

namespace Lorry\Presenter\User;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Model\User;
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

		$flags = array();
		$flags['founder'] = $user->getUsername() === 'B_E';
		$flags['alpha'] = $user->hasFlag(User::FLAG_ALPHA);
		$flags['beta'] = $user->hasFlag(User::FLAG_BETA);
		$flags['vip'] = $user->hasFlag(User::FLAG_VIP);
		$flags['coder'] = $user->hasFlag(User::FLAG_CODER);
		$flags['reporter'] = $user->hasFlag(User::FLAG_REPORTER);
		$this->context['flags'] = $flags;

		if($user->getRegistration()) {
			$this->context['registration'] = date($this->localisation->getFormat(LocalisationService::FORMAT_DATE), $user->getRegistration());
		}

		$this->context['profiles'] = array();
		if($user->getClonkforge()) {
			$this->context['clonkforge'] = array(
				'profile' => sprintf(gettext('%s on the ClonkForge'), $user->getUsername()),
				'url' => sprintf($this->config->get('clonkforge/url'), urlencode($user->getClonkforge())));
		}
		if($user->getGithub()) {
			$this->context['github'] = array(
				'profile' => sprintf(gettext('%s on GitHub'), $user->getGithub()),
				'url' => sprintf($this->config->get('github/url'), urlencode($user->getGithub())));
		}

		$games = ModelFactory::build('Game')->byAnything();

		$releases = ModelFactory::build('Release')->all()->byOwner($user->getId());
		$this->context['addons'] = array();
		foreach($releases as $release) {
			$addon = $release->fetchAddon();
			$user_addon = array(
				'title' => $addon->getTitle(),
				'short' => $addon->getShort(),
				'introduction' => $addon->getIntroduction()
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
