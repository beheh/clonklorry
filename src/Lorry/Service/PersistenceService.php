<?php

namespace Lorry\Service;

use PDO;
use Exception;
use PDOException;
use Lorry\Model;

class PersistenceService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;
	protected $cache;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
		$this->cache = array();
	}

	/**
	 *
	 * @var PDO
	 */
	private $connection = null;

	public function ensureConnected() {
		if($this->connection)
			return true;
		try {
			$this->connection = new PDO(
					$this->config->get('database/dsn'), $this->config->get('database/username'), $this->config->get('database/password'));
		} catch(PDOException $ex) {
			// catch the pdo exception to prevent credential leaking
			throw new Exception('could not connect to database ('. $ex->getMessage().')');
		}
	}

	public function loadAll(Model $model, $pairs, $orderby = 'id', $descending = false) {
		$this->ensureConnected();
		$model->ensureRow($orderby);

		$parameters = '';
		$values = array();
		foreach($pairs as $row => $value) {
			if(empty($values)) {
				$parameters .= ' WHERE ';
			}
			else {
				$parameters .= ' AND ';
			}
			$parameters .= '`'.$row.'` = :'.$row;
			$values[':'.$row] = $value;
		}

		$order = $descending ? 'DESC' : 'ASC';

		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'`'.$parameters.' ORDER BY `'.$orderby.'` '.$order);
		$statement->execute($values);
		if($statement->errorCode() != PDO::ERR_NONE) {
			throw new Exception(print_r($statement->errorInfo(), true));
		}
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

		return $rows;
	}

	public function load(Model $model, $pairs, $orderby = 'id', $descending = false) {
		$this->ensureConnected();

		$parameters = '';
		$values = array();
		foreach($pairs as $row => $value) {
			if(empty($values)) {
				$parameters .= ' WHERE ';
			}
			else {
				$parameters .= ' AND ';
			}
			$parameters .= '`'.$row.'` = :'.$row;
			$values[':'.$row] = $value;
		}

		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'`'.$parameters.' LIMIT 1');
		$statement->execute($values);
		if($statement->errorCode() != PDO::ERR_NONE) {
			throw new Exception(print_r($statement->errorInfo(), true));
		}
		$rows = $statement->fetch(PDO::FETCH_ASSOC);

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