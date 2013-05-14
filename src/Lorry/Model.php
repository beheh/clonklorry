<?php

namespace Lorry;

use Lorry\Service\PersistenceService;

abstract class Model implements ModelInterface {

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

	public function isLoaded() {
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

	public final function setValue($name, $value) {
		if($this->loaded && $this->values[$name] === $value)
			return true;
		$this->changes[$name] = $this->ensureType($name, $value);
		return true;
	}

	public final function getValue($name) {
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

	public final function byAnything() {
		$this->all();
		return $this->byValues();
	}

	protected final function byValue($row, $value) {
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
			$rows = $this->persistence->loadAll($this, $pairs, $this->order_row, $this->order_descending);

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

			$row = $this->persistence->load($this, $pairs, $this->order_row, $this->order_descending);

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
		switch($this->schema[$row]) {
			case 'int':
				return intval($value);
			case 'string':
			default:
				return $value;
		}
	}

	public final function ensureRow($row) {
		if(!array_key_exists($row, $this->schema) && $row != 'id') {
			throw new \InvalidArgumentException('row "' . $row . '" does not exist');
		}
		return true;
	}

	public final function ensureLoaded() {
		if(!$this->loaded) {
			throw new \Exception('model has not been loaded');
		}
		return true;
	}

	public final function ensureUnloaded() {
		if($this->loaded) {
			throw new \Exception('model has already been loaded');
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
			$this->values[$key] = $value;
		}

		$this->loaded = true;

		return true;
	}

	/**
	 * Returns whether unsaved changes remain.
	 * @return boolean True, if unsaved changes are present.
	 */
	public final function modified() {
		if(!empty($this->changes)) {
			return true;
		}
		return false;
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
		if($this->loaded) {
			if(!$this->persistence->update($this, $this->changes)) {
				return false;
			}
		} else {
			if(!$this->persistence->save($this, $this->changes)) {
				return false;
			}
		}
		$this->values = array_merge($this->values, $this->changes);
		$this->rollback();
		$this->loaded = true;
		return true;
	}

}