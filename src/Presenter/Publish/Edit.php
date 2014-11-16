<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Model\User;
use Lorry\Model\Addon;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\ModelValueInvalidException;

class Edit extends Presenter {

	public static function getAddon($id, User $user) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if($addon->getOwner() != $user->getId()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	public static function getNamespaceProposal(Addon $addon) {
		$title = $addon->getTitle();
		$maintitle = strstr($title, ':');
		$cleantitle = $maintitle ? $maintitle : $title;
		return preg_replace('/[^a-z0-9]/', '', strtolower($cleantitle));
	}

	public function get($id) {
		$this->security->requireLogin();

		$addon = Edit::getAddon($id, $this->session->getUser());
		$this->context['addonid'] = $addon->getId();

		$this->context['title'] = sprintf(gettext('Edit %s'), $addon->getTitle());

		if(!isset($this->context['addontitle'])) {
			$this->context['addontitle'] = $addon->getTitle();
		}

		if(isset($_GET['add'])) {
			$this->context['focus_version'] = true;
		}

		$games = ModelFactory::build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}

		if(!isset($this->context['game'])) {
			$game = $addon->fetchGame();
			$this->context['game'] = $game->getShort();
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

		/* Approve */

		$this->context['namespace_proposal'] = self::getNamespaceProposal($addon);

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

		if(!isset($this->context['namespace'])) {
			$this->context['namespace'] = $addon->getProposedShort();
		}

		/* Releases */

		$releases = ModelFactory::build('Release')->all()->order('version')->byAddon($addon->getId());
		$latest = ModelFactory::build('Release')->latest($addon->getId());
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

		$addon = Edit::getAddon($id, $this->session->getUser());

		$released = false;
		$this->context['released'] = $released;
		if(isset($_POST['addon-form']) && !$released) {
			$errors = array();

			$title = trim(filter_input(INPUT_POST, 'title'));
			try {
				$addon->setTitle($title);
				$this->context['addontitle'] = $addon->getTitle();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Title is %s.'), $ex->getMessage());
				$this->context['addontitle'] = $title;
			}

			try {
				$game = ModelFactory::build('Game')->byShort(filter_input(INPUT_POST, 'game'));
				if(!$game) {
					throw new ModelValueInvalidException('invalid');
				}
				$this->context['game'] = $game->getShort();
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
					$addon->save();
					$this->success('addon', gettext('Addon saved.'));
				}
			} else {
				$this->error('addon', implode('<br>', $errors));
			}
		}

		if(isset($_POST['presentation-form'])) {
			$errors = array();

			$introduction = trim(filter_input(INPUT_POST, 'introduction'));
			try {
				$addon->setIntroduction($introduction);
			} catch(ModelValueInvalidException $ex) {
				$this->context['introduction'] = $introduction;
				$errors[] = sprintf(gettext('Introdution is %s.'), $ex->getMessage());
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

			if(empty($errors)) {
				if($addon->modified()) {
					$addon->save();
					$this->success('presentation', gettext('Presentation saved.'));
				}
			} else {
				$this->error('presentation', implode('<br>', $errors));
			}
		}

		if(isset($_POST['approval-form'])) {
			if(!$addon->isApproved()) {
				if($addon->isSubmittedForApproval()) {
					if(isset($_POST['withdraw'])) {
						$addon->withdrawSubmission();
						$addon->save();
					}
				} else {
					$namespace = trim(strtolower(filter_input(INPUT_POST, 'namespace')));
					try {
						$addon->setProposedShort($namespace);
						$this->context['namespace'] = $addon->getShort();
					} catch(ModelValueInvalidException $ex) {
						$this->context['namespace'] = $namespace;
						$errors[] = sprintf(gettext('Namespace is %s.'), $ex->getMessage());
					}
					if(empty($errors)) {
						$submitted = false;
						if(isset($_POST['submit'])) {
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
					}
					// recheck here, since submission could have thrown another error
					if(empty($errors)) {
						if($addon->modified()) {
							$addon->save();
							if(!$submitted) {
								$this->success('approval', gettext('Namespace saved.'));
							}
						}
					} else {
						$this->error('approval', implode('<br>', $errors));
						$this->context['focus_namespace'] = true;
					}
				}
			}
		}

		if(isset($_POST['release-form'])) {
			$version = ltrim(trim(filter_input(INPUT_POST, 'version')), 'v');

			$release = ModelFactory::build('Release');
			$release->setAddon($addon->getId());

			$errors = array();

			try {
				$release->setVersion($version);
				$version = $release->getVersion();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Version is %s.'), $ex->getMessage());
			}
			$this->context['version'] = $version;

			if(ModelFactory::build('Release')->byVersion($version, $addon->getId()) !== false) {
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
