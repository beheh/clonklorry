<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\PersistenceService;
use Lorry\Exception\ModelValueInvalidException;
use InvalidArgumentException;
use RuntimeException;
use Exception;

abstract class Model {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	/**
	 *
	 * @var \Lorry\Service\PersistenceService
	 */
	protected $persistence;

	private $schema;
	private $values;
	private $changes;
	private $loaded;

	public function __construct(ConfigService $config, PersistenceService $persistence) {
		$this->config = $config;
		$this->persistence = $persistence;

		$this->loaded = false;

		$this->schema = $this->getSchema();

		$this->values = array();
		foreach($this->schema as $key => $row) {
			$this->values[$key] = null;
		}

		$this->changes = array();
	}

	/**
	 * @return array
	 */
	abstract public function getSchema();

	/**
	 * @return string
	 */
	abstract public function getTable();

	/**
	 * 
	 * @return bool
	 */
	final public function isLoaded() {
		return $this->loaded;
	}

	/**
	 * 
	 * @return int
	 */
	final public function getId() {
		return $this->getValue('id');
	}

	/**
	 * 
	 * @param int $id
	 * @return Model
	 */
	final public function byId($id) {
		return $this->byValue('id', $id);
	}

	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	final protected function setValue($name, $value) {
		$this->ensureField($name);
		$value = $this->ensureType($name, $value);
		if($this->loaded && $this->getValue($name) === $value) {
			return;
		}
		$this->changes[$name] = $value;
	}

	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	final protected function getValue($name) {
		$this->ensureField($name);
		if(array_key_exists($name, $this->changes))
			return $this->changes[$name];
		$this->ensureLoaded();
		return $this->values[$name];
	}

	private $multiple = false;

	final public function all() {
		$this->ensureUnloaded();

		$this->multiple = true;
		return $this;
	}

	private $order = array();

	/**
	 * 
	 * @param string $row
	 * @param bool $descending
	 * @return \Lorry\Model
	 */
	final public function order($row, $descending = false) {
		$this->ensureField($row);
		$this->order[] = $descending ? $row.' DESC' : $row;
		return $this;
	}

	private $limit_from = null;
	private $limit = null;

	/**
	 * 
	 * @param int $from
	 * @param int $limit
	 * @return \Lorry\Model
	 */
	final public function limit($from, $limit = null) {
		if($limit === null) {
			$limit = $from;
		} else {
			$this->limit_from = $from;
		}
		$this->limit = $limit;
		return $this;
	}

	/**
	 * 
	 * @return array
	 */
	final public function byAnything() {
		$this->all();
		return $this->byValues();
	}

	/**
	 * 
	 * @param string $row
	 * @param mixed $value
	 * @return \Lorry\Model|array
	 * @throws Exception
	 */
	final protected function byValue($row, $value) {
		if(!is_string($row)) {
			throw new Exception('invalid row name');
		}
		return $this->byValues(array($row => $value));
	}

	/**
	 *
	 * @param array $pairs
	 * @return \Lorry\Model|array
	 */
	final protected function byValues($pairs = array()) {
		$this->ensureUnloaded();
		foreach($pairs as $row => $value) {
			$this->ensureField($row);
			// do not allow abstract objects
			if(is_object($value)) {
				throw new Exception('attempting to fetch model using object as value');
			}
		}

		$order = $this->order;
		if(empty($order)) {
			$order[] = 'id';
		}

		if($this->multiple) {
			$rows = $this->persistence->loadAll($this, $pairs, $order, $this->limit_from, $this->limit);

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
				return null;
			}

			$row = $this->persistence->load($this, $pairs, $order, $this->limit_from, $this->limit);

			if(empty($row)) {
				return null;
			}

			$this->unserialize($row);

			return $this;
		}
	}

	/**
	 * 
	 * @param string $row
	 * @param mixed $value
	 * @return bool
	 */
	final protected function match($row, $value) {
		$this->ensureLoaded();
		$this->ensureField($row);

		$comparison = $this->values[$row];
		if(array_key_exists($row, $this->changes)) {
			$comparison = $this->changes[$row];
		}

		if($value === $comparison) {
			return true;
		}

		return false;
	}

	/**
	 * 
	 * @param string $row
	 * @param mixed $value
	 * @return mixed
	 */
	final protected function ensureType($row, $value) {
		$this->ensureField($row);
		if($row == 'id' || $value === null) {
			return $value;
		}
		switch($this->schema[$row]) {
			case 'int':
			case 'datetime':
				$value = intval($value);
				$this->validateNumber($value);
				break;
			case 'boolean':
				$value = ($value ? true : false);
				break;
		}
		return $value;
	}

	/**
	 * 
	 * @param string $row
	 * @param mixed $value
	 * @return mixed
	 */
	private function decodeType($row, $value) {
		$this->ensureField($row);
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

	/**
	 * 
	 * @param string $row
	 * @param mixed $value
	 * @return mixed
	 */
	private function encodeType($row, $value) {
		$this->ensureField($row);
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

	/**
	 * 
	 * @param string $field
	 * @throws InvalidArgumentException
	 */
	final public function ensureField($field) {
		if(!array_key_exists($field, $this->schema) && $field != 'id') {
			throw new InvalidArgumentException('field "'.$field.'" does not exist in model "'.get_class($this).'"');
		}
	}

	/**
	 * 
	 * @throws Exception
	 */
	final public function ensureLoaded() {
		if(!$this->loaded) {
			throw new Exception('model has not been loaded');
		}
	}

	/**
	 * 
	 * @throws Exception
	 */
	final public function ensureUnloaded() {
		if($this->loaded) {
			throw new Exception('model has already been loaded');
		}
	}

	/**
	 * Loads the data into this model instance. Should only be called by the PersistenceService.
	 * @param array $row
	 * @return bool True,
	 */
	final public function unserialize($row) {
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
	 * 
	 * @return array
	 */
	final public function getChanges() {
		return $this->changes;
	}

	/**
	 * Returns whether unsaved changes remain.
	 * @return bool True, if unsaved changes are present.
	 */
	final public function modified() {
		return !empty($this->changes);
	}

	/**
	 * Unsets all changes since last load() or save()
	 */
	final public function rollback() {
		$this->changes = array();
	}

	/**
	 * Ensure that all changes to model are persistent.
	 * @return bool True, if all changes will be persistent
	 */
	final public function save() {
		if(!$this->modified())
			return true;

		$changes = array();
		if(!$this->loaded) {
			$this->onInsert();
		}
		foreach($this->changes as $row => $value) {
			$changes[$row] = $this->encodeType($row, $value);
		}
		if($this->loaded) {
			if(!$this->persistence->update($this, $changes)) {
				return false;
			}
		} else {
			$id = $this->persistence->insert($this, $changes);
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

	/**
	 * Removes the model.
	 * @return bool True, if the model does not exist any more
	 */
	final public function delete() {
		if(!$this->loaded) {
			throw new RuntimeException('model is not persistent');
		}

		if(!$this->persistence->delete($this)) {
			return false;
		}

		$this->loaded = false;
		return true;
	}

	/* Chainloading */

	/**
	 * 
	 * @param string $model The model to create and load
	 * @param string $row The row which maps the target models id
	 * @return Model
	 */
	final protected function fetch($model, $row) {
		$this->ensureLoaded();
		$this->ensureField($row);
		$object = $this->persistence->build($model)->byId($this->getValue($row));
		return $object;
	}

	/* Validation */

	/**
	 *
	 * @param string $string The string to validate
	 * @param int $minlength The minimum length of the string
	 * @param int $maxlength The maximum length of the string
	 * @throws ModelValueInvalidException
	 */
	final protected function validateString($string, $minlength, $maxlength) {
		$string = (string) $string;
		if(strlen($string) < $minlength) {
			throw new ModelValueInvalidException(gettext('too short'));
		}
		if(strlen($string) > $maxlength) {
			throw new ModelValueInvalidException(gettext('too long'));
		}
	}

	/**
	 * 
	 * @param string $email
	 * @throws ModelValueInvalidException
	 */
	final protected function validateEmail($email) {
		$email = (string) $email;

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new ModelValueInvalidException(gettext('not a valid email address'));
		}
	}

	/**
	 * 
	 * @param string $url
	 * @throws ModelValueInvalidException
	 */
	final protected function validateUrl($url) {
		$url = (string) $url;

		if(!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new ModelValueInvalidException(gettext('not a valid URL'));
		}
	}

	/**
	 * 
	 * @param int $number
	 * @throws ModelValueInvalidException
	 */
	final protected function validateNumber($number) {
		if(!is_int($number)) {
			throw new ModelValueInvalidException(gettext('not a valid number'));
		}
	}

	/**
	 * 
	 * @param string $value
	 * @param string $regexp
	 * @throws Exception
	 * @throws ModelValueInvalidException
	 */
	final protected function validateRegexp($value, $regexp) {
		$result = preg_match($regexp, $value);
		if($result === false) {
			throw new Exception('error matching the regular expression');
		} else if(!$result) {
			throw new ModelValueInvalidException(gettext('invalid'));
		}
	}

	/**
	 * 
	 * @param string $language
	 */
	final protected function validateLanguage($language) {
		$this->validateString($language, 5, 5);
	}

	final protected function localizeField($field, $language = null) {
		if($language === null) {
			$language = gettext('en');
		}
		$field .= '_'.$language;
		$this->ensureField($field);
		return $field;
	}

	/**
	 * 
	 * @return array
	 */
	public function forApi() {
		return array();
	}

	/**
	 * 
	 * @return array
	 */
	public function forPresenter() {
		return array();
	}

	protected function onInsert() {
		
	}

}
