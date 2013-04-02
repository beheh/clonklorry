<?php

class Lorry_Service_Persistence extends Lorry_Service {

	/**
	 *
	 * @var PDO
	 */
	private $connection = null;

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		$this->connection = new PDO(
			$this->lorry->config->database['dsn'],
			$this->lorry->config->database['username'],
			$this->lorry->config->database['password']);
		return true;
	}

	public function get($model_name) {
		$model_class = 'Lorry_Model_'.ucfirst($model_name);
		if(!class_exists($model_class)) {
			throw new UnexpectedValueException('unknown model');
		}
		$model = new $model_class($this->lorry);
		return $model;
	}

	public function query(Lorry_Model $model, $row, $value) {
		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'` WHERE `'.$row.'` = :value LIMIT 1');
		$statement->execute(array(':value' => $value));
		if($statement->errorCode() != PDO::ERR_NONE) {
			throw new Exception(print_r($statement->errorInfo(), true));
		}
		return $statement->fetch(PDO::FETCH_ASSOC);
	}
}

