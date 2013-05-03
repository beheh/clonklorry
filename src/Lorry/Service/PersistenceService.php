<?php

namespace Lorry\Service;

use PDO;
use Exception;
use Lorry\Model;

class PersistenceService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}


	/**
	 *
	 * @var PDO
	 */
	private $connection = null;

	public function ensureConnected() {
		if($this->connection)
			return true;
		$this->connection = new PDO(
		  $this->config->get('database/dsn'), $this->config->get('database/username'), $this->config->get('database/password'));
	}

	public function load(Model $model, $row, $value) {
		$this->ensureConnected();

		//$cache = $this->lorry->cache->lookup(array($model->getTable(), $row, $value));
		//if($cache)
			//return $cache;

		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'` WHERE `'.$row.'` = :value LIMIT 1');
		$statement->execute(array(':value' => $value));
		if($statement->errorCode() != \PDO::ERR_NONE) {
			throw new Exception(print_r($statement->errorInfo(), true));
		}
		$rows = $statement->fetch(\PDO::FETCH_ASSOC);
		//$this->lorry->cache->set(array($model->getTable(), $row, $value), $rows);

		return $rows;
	}

	public function update(Model $model, $changes) {
		$this->ensureConnected();
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
			throw new Exception(print_r($query->errorInfo(), true));
		}
		return $query->rowCount() == 1;
	}

	public function save(Model $model, $values) {
		$this->ensureConnected();
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
			throw new Exception(print_r($query->errorInfo(), true));
		}
		return $query->rowCount() == 1;
	}

}