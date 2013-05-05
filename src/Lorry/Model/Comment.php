<?php

namespace Lorry\Model;

use Lorry\Model;

class Comment extends Model {

	public function __construct() {
		parent::__construct('comment', array(
			'owner' => 'int',
			'content' => 'text',
			'timestamp' => 'int'));
	}

	public function setOwner($owner) {
		return $this->setValue('owner', $owner);
	}

	public function getOwner() {
		return $this->getValue('owner');
	}

	public function byOwner($owner) {
		return $this->byValue('owner', $owner);
	}

	public function setContent($content) {
		return $this->setValue('content', $content);
	}

	public function getContent() {
		return $this->getValue('content');
	}

	public function setTimestamp($timestamp) {
		return $this->setValue('timestamp', $timestamp);
	}

	public function getTimestamp() {
		return $this->getValue('timestamp');
	}

	public function __toString() {
		return $this->getUsername().'';
	}

}