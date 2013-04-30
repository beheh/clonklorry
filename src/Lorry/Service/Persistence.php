<?php

namespace Lorry\Service;

use Lorry\Service;
use Lorry\Environment;
use Lorry\Model;

class Persistence extends Service {

	/**
	 *
	 * @var PDO
	 */
	private $connection = null;

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		$this->connection = new \PDO(
		  $this->lorry->config->database['dsn'], $this->lorry->config->database['username'], $this->lorry->config->database['password']);
		return true;
	}

	public function get($model_name) {
		$model_class = '\\Lorry\\Models\\'.ucfirst($model_name);
		if(!class_exists($model_class)) {
			throw new \UnexpectedValueException('unknown model '.$model_class);
		}
		$model = new $model_class($this->lorry);
		return $model;
	}

	public function load(Model $model, $row, $value) {
		$cache = $this->lorry->cache->lookup(array($model->getTable(), $row, $value));
		if($cache) return $cache;

		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'` WHERE `'.$row.'` = :value LIMIT 1');
		$statement->execute(array(':value' => $value));
		if($statement->errorCode() != \PDO::ERR_NONE) {
			throw new \Exception(print_r($statement->errorInfo(), true));
		}
		$rows = $statement->fetch(\PDO::FETCH_ASSOC);
		$this->lorry->cache->set(array($model->getTable(), $row, $value), $rows);

		return $rows;
	}

	public function update(Model $model, $changes) {
		$model->ensureLoaded();
		$values = array();
		$sets = '';
		foreach($changes as $row => $change) {
			if(!empty($values))
				$sets .= ', ';
			$sets .= '`'.$row.'` = :'.$row;
			$values[':'.$row] = $change;
		}
		$values['id'] = $model->getId();
		$query = $this->connection->prepare('UPDATE `'.$model->getTable().'` SET '.$sets.' WHERE `id` = :id');
		$query->execute($values);
		if($query->errorCode() !== '00000') {
			throw new \Exception(print_r($query->errorInfo(), true));
		}
		return $query->rowCount() == 1;
	}

	public function save(Model $model, $values) {
		$model->ensureUnloaded();

		$keys = '';
		$valuenames = '';
		$contents = array();
		foreach($values as $key => $value) {
			if(!empty($contents)) {
				$keys .= ', ';
				$valuenames .= ', ';
			}
			$keys .= '`'.$key.'`';
			$valuenames .= ':'.$key;
			$contents[':'.$key] = $value;
		}
		$query = $this->connection->prepare('INSERT INTO `'.$model->getTable().'` ('.$keys.') VALUES ('.$valuenames.')');
		$query->execute($contents);
		if($query->errorCode() !== '00000') {
			throw new \Exception(print_r($query->errorInfo(), true));
		}
		return $query->rowCount() == 1;
	}

}