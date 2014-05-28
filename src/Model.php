<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\PersistenceService;
use Lorry\Exception\ModelValueInvalidException;
use InvalidArgumentException;
use Exception;

abstract class Model implements ModelInterface {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	/**
	 *
	 * @var \Lorry\Service\PersistenceService
	 */
	protected $persistence;

	public function setPersistenceService(PersistenceService $persistence) {
		$this->persistence = $persistence;
	}

	private $table;
	private $schema;
	private $values;
	private $changes;
	private $loaded;

	public function __construct($table, $rows) {
		$this->loaded = false;

		$this->table = $table;
		$this->schema = $rows;

		$this->values = array();
		foreach($this->schema as $key => $row) {
			$this->values[$key] = null;
		}

		$this->changes = array();
	}

	public final function isLoaded() {
		return $this->loaded;
	}

	public final function getTable() {
		return $this->table;
	}

	public final function getSchema() {
		return $this->schema;
	}

	public final function getId() {
		return $this->getValue('id');
	}

	protected final function setValue($name, $value) {
		$this->ensureRow($name);
		$value = $this->ensureType($name, $value);
		if($this->loaded && $this->values[$name] === $value)
			return true;
		$this->changes[$name] = $value;
		return true;
	}

	protected final function getValue($name) {
		$this->ensureRow($name);
		if(array_key_exists($name, $this->changes))
			return $this->changes[$name];
		$this->ensureLoaded();
		return $this->values[$name];
	}

	public final function byId($id) {
		return $this->byValue('id', $id);
	}

	private $multiple = false;

	public final function all() {
		$this->ensureUnloaded();

		$this->multiple = true;
		return $this;
	}

	private $order_row = 'id';
	private $order_descending = false;

	public final function order($row, $descending = false) {
		$this->order_row = $row;
		$this->order_descending = $descending;
		return $this;
	}

	private $limit_from = null;
	private $limit = null;

	public final function limit($from, $limit = null) {
		if($limit == null) {
			$limit = $from;
		}
		else {
			$this->limit_from = $from;
		}
		$this->limit = $limit;
		return $this;
	}

	public final function byAnything() {
		$this->all();
		return $this->byValues();
	}

	protected final function byValue($row, $value) {
		if(!is_string($row)) {
			throw new Exception('invalid row name');
		}
		return $this->byValues(array($row => $value));
	}

	/**
	 *
	 * @param array $pairs
	 */
	protected final function byValues($pairs = array()) {
		$this->ensureUnloaded();
		foreach($pairs as $row => $value) {
			$this->ensureRow($row);
			// do not allow abstract objects
			if(is_object($value)) {
				throw new Exception('attempting to fetch model using object as value');
			}
			// do not allow empty values to search
			if(empty($value)) {
				if($this->multiple) {
					return array();
				} else {
					return false;
				}
			}
		}

		if($this->multiple) {
			$rows = $this->persistence->loadAll($this, $pairs, $this->order_row, $this->order_descending, $this->limit_from, $this->limit);

			if(empty($rows)) {
				return array();
			}

			$instances = array();
			foreach($rows as $row) {
				$instance = clone $this;
				$instance->unserialize($row);
				$instances[] = $instance;
			}

			return $instances;
		} else {
			if(empty($pairs)) {
				return false;
			}

			$row = $this->persistence->load($this, $pairs, $this->order_row, $this->order_descending, $this->limit_from, $this->limit);

			if(empty($row)) {
				return false;
			}

			$this->unserialize($row);

			return $this;
		}
	}

	protected final function match($row, $value) {
		$this->ensureLoaded();
		$this->ensureRow($row);

		if(array_key_exists($row, $this->changes)) {
			if($this->changes[$row] != $value) {
				return false;
			}

			return true;
		}

		if($this->values[$row] != $value) {
			return false;
		}

		return true;
	}

	protected final function ensureType($row, $value) {
		$this->ensureRow($row);
		if($row == 'id' || $value === NULL) {
			return $value;
		}
		switch($this->schema[$row]) {
			case 'int':
			case 'datetime':
				$value = intval($value);
				$this->validateNumber($value);
				break;
			case 'boolean':
				$value = ($value == true);
				break;
		}
		return $value;
	}

	private final function decodeType($row, $value) {
		$this->ensureRow($row);
		if($row == 'id' || $value === NULL) {
			return $value;
		}
		switch($this->schema[$row]) {
			case 'datetime':
				$value = strtotime($value);
				break;
		}
		return $value;
	}

	private final function encodeType($row, $value) {
		$this->ensureRow($row);
		if($row == 'id' || $value === NULL) {
			return $value;
		}
		switch($this->schema[$row]) {
			case 'datetime':
				$this->validateNumber($value);
				$value = date('Y-m-d H:i:s', $value);
				break;
		}
		return $value;
	}

	public final function ensureRow($row) {
		if(!array_key_exists($row, $this->schema) && $row != 'id') {
			throw new InvalidArgumentException('row "'.$row.'" does not exist');
		}
		return true;
	}

	public final function ensureLoaded() {
		if(!$this->loaded) {
			throw new Exception('model has not been loaded');
		}
		return true;
	}

	public final function ensureUnloaded() {
		if($this->loaded) {
			throw new Exception('model has already been loaded');
		}
		return true;
	}

	/**
	 * Loads the data into this model instance. Should only be called by the PersistenceService.
	 * @param array $row
	 * @return boolean True,
	 */
	public final function unserialize($row) {
		if(empty($row) || !isset($row['id']) || !is_numeric($row['id'])) {
			return false;
		}

		$this->rollback();

		foreach($row as $key => $value) {
			$this->values[$key] = $this->ensureType($key, $this->decodeType($key, $value));
		}

		$this->loaded = true;

		return true;
	}

	/**
	 * Returns whether unsaved changes remain.
	 * @return boolean True, if unsaved changes are present.
	 */
	public final function modified() {
		return !empty($this->changes);
	}

	/**
	 * Unsets all changes since last load() or save()
	 */
	public final function rollback() {
		$this->changes = array();
	}

	/**
	 * Ensure that all changes to model are persistent.
	 * @return boolean True, if all changes will be persistent
	 */
	public final function save() {
		if(!$this->modified())
			return true;

		$changes = array();
		foreach($this->changes as $row => $value) {
			$changes[$row] = $this->encodeType($row, $value);
		}
		if($this->loaded) {
			if(!$this->persistence->update($this, $changes)) {
				return false;
			}
		} else {
			$this->onSave();
			$id = $this->persistence->save($this, $changes);
			if(!$id) {
				return false;
			}
			$this->changes['id'] = $id;
		}
		$this->values = array_merge($this->values, $this->changes);

		$this->rollback();
		$this->loaded = true;
		return true;
	}

	/* Validation */

	/**
	 *
	 * @param type $string The string to validate
	 * @param type $minlength The minimum length of the string
	 * @param type $maxlength The maximum length of the string
	 * @param type $regexp The (optional) regexp to match the string agains
	 * @throws ModelValueInvalidException
	 */
	protected final function validateString($string, $minlength, $maxlength, $regexp = '') {
		$string = (string) $string;
		if(strlen($string) < $minlength) {
			throw new ModelValueInvalidException(gettext('too short'));
		}
		if(strlen($string) > $maxlength) {
			throw new ModelValueInvalidException(gettext('too long'));
		}
		if(!empty($regexp)) {
			/* if(!preg_match($pattern, $subject)) {
			  throw new ModelValueInvalidException(gettext('not in a valid format'));
			  } */
		}
	}

	protected final function validateEmail($email) {
		$email = (string) $email;

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new ModelValueInvalidException(gettext('not a valid email address'));
		}
	}

	protected final function validateUrl($url) {
		$url = (string) $url;

		if(!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new ModelValueInvalidException(gettext('not a valid URL'));
		}
	}

	protected final function validateNumber($number) {
		if(!is_int($number)) {
			throw new ModelValueInvalidException(gettext('not a valid number'));
		}
	}

	protected final function validateRegexp($value, $regexp) {
		$result = preg_match($regexp, $value);
		if($result === false) {
			throw new Exception('error matching the regular expression');
		} else if(!$result) {
			throw new ModelValueInvalidException(gettext('invalid'));
		}
	}

	protected final function validateLanguage($language) {
		$this->validateString($language, 5, 5);
	}

	public function forApi() {
		return array();
	}

	protected function onSave() {
	}
}
