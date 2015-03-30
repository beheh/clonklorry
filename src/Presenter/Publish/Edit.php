<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\Model\User;
use Lorry\Model\Addon;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\ModelValueInvalidException;

class Edit extends Presenter {

	public static function getAddon($persistence, $id, User $user) {
		$addon = $persistence->build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if($addon->getOwner() != $user->getId()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	public static function getNamespaceProposal(Addon $addon) {
		$title = $addon->getTitle('en');
		if(empty($title)) {
			$title = $addon->getTitle('de');
		}
		$maintitle = strstr($title, ':');
		$cleantitle = $maintitle ? $maintitle : $title;
		return preg_replace('/[^a-z0-9]/', '', strtolower($cleantitle));
	}

	public function get($id) {
		$this->security->requireLogin();

		$addon = Edit::getAddon($this->persistence, $id, $this->session->getUser());

		/* Approval */

		$this->context['approval_comment'] = $addon->getApprovalComment();

		if($addon->isApproved()) {
			$this->context['approved'] = true;
			$this->context['namespace'] = $addon->getShort();
		} elseif($addon->isRejected()) {
			$this->context['rejected'] = true;
		} else {
			if($addon->isSubmittedForApproval()) {
				$this->context['submitted'] = true;
			}
		}

		/* Basic */

		$this->context['addonid'] = $addon->getId();
		$this->context['title'] = sprintf(gettext('Edit %s'), $addon->getTitle());
		$this->context['heading'] = $addon->getTitle();

		$this->context['title_placeholder'] = $addon->getTitle();
		foreach($this->localisation->getLocalizedCountries() as $country) {
			if(!isset($this->context['title_' . $country])) {
				$this->context['title_' . $country] = $addon->getTitle($country);
			}
		}

		$this->context['namespace_proposal'] = self::getNamespaceProposal($addon);
		if(!isset($this->context['namespace'])) {
			$this->context['namespace'] = $addon->getProposedShort();
		}

		$games = $this->persistence->build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}
		if(!isset($this->context['game'])) {
			$game = $addon->fetchGame();
			$this->context['game'] = $game->forPresenter();
		}

		if(!isset($this->context['abbreviation'])) {
			$this->context['abbreviation'] = $addon->getAbbreviation();
		}

		/* Presentation */

		if(!isset($this->context['introduction'])) {
			$this->context['introduction'] = $addon->getIntroduction();
		}

		if(!isset($this->context['description'])) {
			$this->context['description'] = $addon->getDescription();
		}

		if(!isset($this->context['website'])) {
			$this->context['website'] = $addon->getWebsite();
		}

		if(!isset($this->context['bugtracker'])) {
			$this->context['bugtracker'] = $addon->getBugtracker();
		}

		if(!isset($this->context['forum'])) {
			$this->context['forum'] = $addon->getForum();
		}


		/* Releases */

		if(isset($_GET['add'])) {
			$this->context['focus_version'] = true;
		}

		// fetch all releasees for this addon and roughly sort them
		$releases_raw = $this->persistence->build('Release')->all()->order('timestamp')->order('version')->byAddon($addon->getId());
		// move unpublished releases to end of list
		$releases = array();
		$unreleased = array();
		foreach($releases_raw as $release) {
			if($release->isReleased()) {
				$releases[] = $release;
			} else {
				$unreleased[] = $release;
			}
		}
		$releases = array_merge($releases, $unreleased);
		$latest = $this->persistence->build('Release')->latest($addon->getId());
		$this->context['releases'] = array();
		foreach($releases as $release) {
			$this->context['releases'][$release->getId()] = array(
				'version' => $release->getVersion(),
				'released' => $release->isReleased(),
				'latest' => ($latest && $latest->getId() == $release->getId()),
				'scheduled' => $release->isScheduled());
		}

		$this->display('publish/edit.twig');
	}

	public function post($id) {
		$this->security->requireLogin();

		$this->security->requireValidState();

		$addon = Edit::getAddon($this->persistence, $id, $this->session->getUser());

		// @todo released?
		$released = false;
		$this->context['released'] = $released;

		/* Basic */

		if(isset($_POST['addon-form'])) {
			if($addon->isSubmittedForApproval() || $addon->isApproved()) {
				if(isset($_POST['withdraw']) && !$addon->isApproved()) {
					$addon->withdrawSubmission();
					$addon->save();
				}
			} else if(!$released && !isset($_POST['withdraw'])) {
				$errors = array();

				$title_en = trim(filter_input(INPUT_POST, 'title_en'));
				try {
					$addon->setTitle($title_en, 'en');
					$this->context['title_en'] = $addon->getTitle('en');
				} catch(ModelValueInvalidException $ex) {
					$errors[] = sprintf(gettext('English title is %s.'), $ex->getMessage());
					$this->context['title_en'] = $title_en;
				}

				$title_de = trim(filter_input(INPUT_POST, 'title_de'));
				try {
					$addon->setTitle($title_de, 'de');
					$this->context['title_de'] = $addon->getTitle('de');
				} catch(ModelValueInvalidException $ex) {
					$errors[] = sprintf(gettext('German title is %s.'), $ex->getMessage());
					$this->context['title_de'] = $title_de;
				}

				$namespace = trim(strtolower(filter_input(INPUT_POST, 'namespace')));
				try {
					$addon->setProposedShort($namespace);
					$this->context['namespace'] = $addon->getShort();
				} catch(ModelValueInvalidException $ex) {
					$this->context['namespace'] = $namespace;
					$errors[] = sprintf(gettext('Namespace is %s.'), $ex->getMessage());
				}

				$submitted = false;
				if(isset($_POST['submit'])) {
					if($addon->getTitle('en') === null) {
						try {
							$addon->setTitle($addon->getTitle(), 'en');
						} catch(ModelValueInvalidException $ex) {
							// don't care - submit for approval will complain about missing title
						}
					}
					if($addon->getTitle('de') === null) {
						try {
							$addon->setTitle($addon->getTitle(), 'de');
						} catch(ModelValueInvalidException $ex) {
							// don't care - see above
						}
					}
					if($addon->getProposedShort() === null) {
						$proposal = self::getNamespaceProposal($addon);
						try {
							$addon->validateAddonShort($proposal);
							$addon->setProposedShort($proposal);
						} catch(ModelValueInvalidException $ex) {
							// if user has not supplied proposal and ours doesn't work - ignore, it will be re-caught when setting
						}
					}
					try {
						$addon->submitForApproval();
						$submitted = true;
					} catch(ModelValueInvalidException $ex) {
						$this->context['namespace'] = $namespace;
						$errors[] = gettext('Namespace required.');
					}
				}

				try {
					$game = $this->persistence->build('Game')->byShort(filter_input(INPUT_POST, 'game'));
					if(!$game) {
						throw new ModelValueInvalidException('invalid');
					}
					$this->context['game'] = $game->forPresenter();
					$addon->setGame($game->getId());
				} catch(ModelValueInvalidException $ex) {
					$errors[] = sprintf(gettext('Game is %s.'), $ex->getMessage());
				}

				$abbreviation = trim(filter_input(INPUT_POST, 'abbreviation'));
				try {
					$addon->setAbbreviation($abbreviation);
					$this->context['abbreviation'] = $addon->getAbbreviation();
				} catch(ModelValueInvalidException $ex) {
					$errors[] = sprintf(gettext('Abbreviation is %s.'), $ex->getMessage());
					$this->context['abbreviation'] = $abbreviation;
				}

				if(empty($errors)) {
					if($addon->modified()) {
						if($submitted) {
							$existing = $this->persistence->build('Addon')->byShort($addon->getProposedShort());
							if($existing) {
								$addon->reject(gettext('An addon has already reserved this namespace.'));
							}
							$addon->save();
						} else {
							$addon->save();
							$this->success('addon', gettext('Addon saved.'));
						}
					}
				} else {
					$this->error('addon', implode('<br>', $errors));
				}
			}
		}

		/* Presentation */

		if(isset($_POST['presentation-form'])) {
			$errors = array();

			$introduction = trim(filter_input(INPUT_POST, 'introduction'));
			try {
				$addon->setIntroduction($introduction);
			} catch(ModelValueInvalidException $ex) {
				$this->context['introduction'] = $introduction;
				$errors[] = sprintf(gettext('Introduction is %s.'), $ex->getMessage());
			}

			$description = trim(filter_input(INPUT_POST, 'description'));
			try {
				$addon->setDescription($description);
			} catch(ModelValueInvalidException $ex) {
				$this->context['description'] = $description;
				$errors[] = sprintf(gettext('Description is %s.'), $ex->getMessage());
			}

			$website = trim(filter_input(INPUT_POST, 'website-url'));
			try {
				$addon->setWebsite($website);
			} catch(ModelValueInvalidException $ex) {
				$this->context['website'] = $website;
				$errors[] = sprintf(gettext('Website is %s.'), $ex->getMessage());
			}

			$bugtracker = trim(filter_input(INPUT_POST, 'bugtracker-url'));
			try {
				$addon->setBugtracker($bugtracker);
			} catch(ModelValueInvalidException $ex) {
				$this->context['bugtracker'] = $bugtracker;
				$errors[] = sprintf(gettext('Bugtracker is %s.'), $ex->getMessage());
			}

			$forum = trim(filter_input(INPUT_POST, 'forum-url'));
			try {
				$addon->setForum($forum);
			} catch(ModelValueInvalidException $ex) {
				$this->context['forum'] = $forum;
				$errors[] = sprintf(gettext('Forum is %s.'), $ex->getMessage());
			}

			if(empty($errors)) {
				if($addon->modified()) {
					$addon->save();
					$this->success('presentation', gettext('Presentation saved.'));
				}
			} else {
				$this->error('presentation', implode('<br>', $errors));
			}
		}

		/* Releases */

		if(isset($_POST['releases-form'])) {
			$version = trim(filter_input(INPUT_POST, 'version'));

			$release = $this->persistence->build('Release');
			$release->setAddon($addon->getId());

			$errors = array();

			try {
				$release->setVersion($version);
				$version = $release->getVersion();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Version is %s.'), $ex->getMessage());
			}
			$this->context['version'] = $version;

			if($this->persistence->build('Release')->byVersion($version, $addon->getId()) !== null) {
				$errors[] = gettext('Version already exists.');
			}

			if(empty($errors)) {
				$release->save();
				$this->success('release', gettext('Release created.'));
			} else {
				$this->error('release', implode('<br>', $errors));
				$this->context['focus_version'] = true;
			}
		}

		$this->get($id);
	}

}
