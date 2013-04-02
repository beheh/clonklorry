<?php

abstract class Lorry_Model extends Lorry_Object {

	private $table;
	private $rows;
	private $values;
	private $changes;
	private $loaded;

	public function __construct(Lorry_Environment $lorry, $table, $rows) {
		parent::__construct($lorry);

		$this->loaded = false;

		$this->table = $table;
		$this->rows = $rows;

		$this->values = array();
		foreach($this->rows as $row) {
			$this->values[$row] = null;
		}

		$this->changes = array();
	}

	public function isLoaded() {
		return $this->loaded;
	}

	public final function __set($row, $value) {
		$this->ensureLoaded();
		$this->ensureRow($row);
		$this->changes[$row] = $value;
	}

	public final function __get($row) {
		$this->ensureLoaded();
		$this->ensureRow($row);
		if(isset($this->changes[$row]))
			return $this->changes[$row];
		return $this->values[$row];
	}

	public final function getTable() {
		return $this->table;
	}

	public final function getId() {
		return $this->getValue('id');
	}

	public final function getValue($name) {
		try {
			return $this->$name;
		} catch(Exception $ex) {
			return null;
		}
	}

	public final function byId($id) {
		return $this->byValue('id', $id);
	}

	protected final function byValue($row, $value) {
		$this->ensureUnloaded();
		$this->ensureRow($row);

		//@TODO
		$row = $this->lorry->persistence->query($this, $row, $value);
		if(empty($row)) {
			return false;
		}

		foreach($row as $key => $value) {
			$this->values[$key] = $value;
		}

		$this->loaded = true;
		return $this;
	}

	protected final function match($row, $value) {
		$this->ensureLoaded();
		$this->ensureRow($row);

		if($this->values[$row] != $value) {
			return false;
		}

		return true;
	}

	protected final function ensureRow($row) {
		if(!in_array($row, $this->rows) && $row != 'id') {
			throw new InvalidArgumentException('row "'.$name.'" does not exist');
		}
		return true;
	}

	protected final function ensureLoaded() {
		if(!$this->loaded) {
			throw new Exception('model has not been loaded');
		}
		return true;
	}

	protected final function ensureUnloaded() {
		if($this->loaded) {
			throw new Exception('model has already been loaded');
		}
		return true;
	}

	public final function save() {
		if(empty($this->changes))
			return true;
	}

}

