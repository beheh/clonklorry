<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ModelFactory;

class Addon extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'owner' => 'string',
			'short' => 'string',
			'title' => 'string',
			'abbreviation' => 'string',
			'game' => 'int',
			'type' => 'int',
			'introduction' => 'text',
			'description' => 'text',
			'website' => 'url',
			'bugtracker' => 'url',
			'proposed_short' => 'string',
			'approval_submit' => 'datetime'));
	}

	public function setOwner($owner) {
		return $this->setValue('owner', $owner);
	}

	public function byOwner($owner) {
		return $this->byValue('owner', $owner);
	}

	public function getOwner() {
		return $this->getValue('owner');
	}

	public function fetchOwner() {
		return ModelFactory::build('User')->byId($this->getOwner());
	}
	
	public function validateAddonShort($short) {
		return $this->validateString($short, 4, 30);
	}

	public function setShort($short) {
		$short = trim(strtolower($short));
		if($short) {
			$this->validateAddonShort($short);
		} else {
			$short = null;
		}
		return $this->setValue('short', $short);
	}

	public function byShort($short, $game) {
		$constraints = array('short' => $short, 'game' => $game);
		return $this->byValues($constraints);
	}

	public function getShort() {
		return $this->getValue('short');
	}

	public function setTitle($title) {
		$title = trim($title);
		$this->validateString($title, 3, 50);
		return $this->setValue('title', $title);
	}

	public function getTitle() {
		return $this->getValue('title');
	}

	public function byTitle($title, $owner = 0, $game = 0) {
		$constraints = array('title' => $title);
		if($owner != 0) {
			$constraints['owner'] = $owner;
		}
		if($game != 0) {
			$constraints['game'] = $game;
		}
		return $this->byValues($constraints);
	}

	public function setAbbreviation($abbreviation) {
		$abbreviation = trim(strtolower($abbreviation));
		if($abbreviation) {
			$this->validateString($abbreviation, 2, 6);
		} else {
			$abbreviation = null;
		}
		return $this->setValue('abbreviation', $abbreviation);
	}

	public function getAbbreviation() {
		return $this->getValue('abbreviation');
	}

	public function byAbbreviation($abbreviation, $game) {
		$constraints = array('abbreviation' => $abbreviation, 'game' => $game);
		return $this->byValues($constraints);
	}

	public function setGame($game) {
		return $this->setValue('game', $game);
	}

	public function byGame($game) {
		return $this->byValue('game', $game);
	}

	public function getGame() {
		return $this->getValue('game');
	}

	public function fetchGame() {
		return ModelFactory::build('Game')->byId($this->getGame());
	}

	public function setType($type) {
		return $this->setValue('type', $type);
	}

	public function getType() {
		return $this->getValue('type');
	}

	public function setIntroduction($introduction) {
		if($introduction) {
			$this->validateString($introduction, 50, 200);
		} else {
			$introduction = null;
		}
		return $this->setValue('introduction', $introduction);
	}

	public function getIntroduction() {
		return $this->getValue('introduction');
	}

	public function setDescription($description) {
		if($description) {
			$this->validateString($description, 0, 4096);
		} else {
			$description = null;
		}
		return $this->setValue('description', $description);
	}

	public function getDescription() {
		return $this->getValue('description');
	}

	public function setWebsite($website) {
		if($website) {
			$this->validateUrl($website);
		} else {
			$website = null;
		}
		return $this->setValue('website', $website);
	}

	public function getWebsite() {
		return $this->getValue('website');
	}

	public function setBugtracker($bugtracker) {
		if($bugtracker) {
			$this->validateUrl($bugtracker);
		} else {
			$bugtracker = null;
		}
		return $this->setValue('bugtracker', $bugtracker);
	}

	public function getBugtracker() {
		return $this->getValue('bugtracker');
	}
	
	public function isApproved() {
		return $this->getShort() !== null;
	}
	
	public function byApprovalSubmitted() {
		$constraints = array('short' => array('=', null));
		$this->all()->order('approval_submit');
		return $this->byValues($constraints);
	}
	
	public function setProposedShort($proposed_short) {
		if($proposed_short) {
			$this->validateAddonShort($proposed_short);
		}
		else {
			$proposed_short = null;
		}
		return $this->setValue('proposed_short', $proposed_short);
	}
	
	public function getProposedShort() {
		return $this->getValue('proposed_short');
	}
	
	public function setApprovalSubmit($approval_submit) {
		return $this->setValue('approval_submit', $approval_submit);
	}

	public function getApprovalSubmit() {
		return $this->getValue('approval_submit');
	}

	
	public function __toString() {
		return $this->getTitle().'';
	}

	public function forApi($detailed = false) {
		$result = array();

		$result['id'] = $this->getShort();
		$result['title'] = $this->getTitle();
		if($this->getAbbreviation()) {
			$result['abbreviation'] = $this->getAbbreviation();
		}
		$result['introduction'] = $this->getIntroduction();
		if($detailed) {
			$result['description'] = $this->getDescription();
		}

		return $result;
	}

}
