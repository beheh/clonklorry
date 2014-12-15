<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Service\LocalisationService;
use Lorry\Exception\ModelValueInvalidException;

class Approve extends Presenter {

	/**
	 * 
	 * @param type $id
	 * @return \Lorry\Model\Addon
	 * @throws FileNotFoundException
	 * @throws ForbiddenException
	 */
	public static function getAddon($id) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if(!$addon->isSubmittedForApproval() && !$addon->isApproved() && !$addon->isRejected()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	public function get($id) {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$addon = self::getAddon($id);
		// if not submitted

		$game = $addon->fetchGame();
		$owner = $addon->fetchOwner();

		$this->context['addon'] = $addon->getTitle();
		$this->context['user'] = $owner->forPresenter();

		if($addon->isApproved()) {
			$this->context['approved'] = true;
			$this->context['namespace'] = $addon->getShort();
		} else {
			$this->context['namespace'] = $addon->getProposedShort();
			$this->context['duplicate'] = (ModelFactory::build('Addon')->byShort($addon->getProposedShort()) !== null);
		}
		$this->context['rejected'] = $addon->isRejected();

		$this->context['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
		$this->context['timestamp'] = date($this->localisation->getFormat(LocalisationService::FORMAT_DATETIME), $addon->getApprovalSubmit());

		if(!isset($this->context['comment'])) {
			$this->context['comment'] = $addon->getApprovalComment();
		}
		$this->context['comment_help'] = sprintf(gettext('Please comment in %s, the users language.'), $this->localisation->namedLanguage($owner->getLanguage()));

		$this->display('manage/approve.twig');
	}

	public function post($id) {
		$this->security->requireModerator();
		$this->security->requireIdentification();

		$this->security->requireValidState();

		$addon = self::getAddon($id);

		$errors = array();

		$comment = null;
		if(isset($_POST['comment'])) {
			$comment = filter_input(INPUT_POST, 'comment');
		}
		$this->context['comment'] = $comment;

		try {
			if(isset($_POST['reject'])) {
				if(empty($comment)) {
					$errors[] = gettext('Comment can\'t be empty when rejecting the addon.');
				} else {
					$addon->reject($comment);
				}
			} else if(isset($_POST['approve'])) {
				$addon->approve($comment);
			}
		} catch(ModelValueInvalidException $ex) {
			$errors[] = sprintf(gettext('Comment is %s.'), $ex->getMessage());
		}

		if((isset($_POST['approve']) && ModelFactory::build('Addon')->byShort($addon->getProposedShort()) !== null)) {
			$errors[] = gettext('The requested namespace has already been reserved for another addon.');
		}

		if(empty($errors)) {
			if($addon->modified()) {
				$addon->save();
			}
		} else {
			$this->error('approval', implode('<br>', $errors));
		}

		$this->get($id);
	}

}
