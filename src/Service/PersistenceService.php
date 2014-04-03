<?php

namespace Lorry\Service;

use Analog;
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
			$this->connection = new PDO($this->config->get('database/dsn'), $this->config->get('database/username'), $this->config->get('database/password'));
		} catch(PDOException $ex) {
			// catch the pdo exception to prevent credential leaking
			throw new Exception('could not connect to database ('.$ex->getMessage().')');
		}
	}

	public function loadAll(Model $model, $pairs, $orderby, $descending, $from, $limit) {
		$this->ensureConnected();
		$model->ensureRow($orderby);

		$parameters = '';
		$values = array();
		foreach($pairs as $row => $value) {
			if(empty($values)) {
				$parameters .= ' WHERE ';
			} else {
				$parameters .= ' AND ';
			}
			$parameters .= '`'.$row.'` = ?';
			$values[] = $value;
		}

		$order = $descending ? 'DESC' : 'ASC';

		$limitquery = '';
		if($limit !== null) {
			$limitquery = ($from === null) ? ' LIMIT '.intval($limit) : ' LIMIT '.intval($from).', '.intval($limit);
		}

		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'`'.$parameters.' ORDER BY `'.$orderby.'` '.$order.$limitquery);
		$statement->execute($values);
		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

		return $rows;
	}

	public function load(Model $model, $pairs, $orderby, $descending, $from, $limit) {
		$this->ensureConnected();

		$parameters = '';
		$values = array();
		foreach($pairs as $row => $value) {
			if(empty($values)) {
				$parameters .= ' WHERE ';
			} else {
				$parameters .= ' AND ';
			}
			$parameters .= '`'.$row.'` = ?';
			$values[] = $value;
		}

		$order = $descending ? 'DESC' : 'ASC';

		$limitquery = '';
		if($limit !== null) {
			$limitquery = ($from === null) ? ' LIMIT '.intval($limit) : ' LIMIT '.intval($from).', '.intval($limit);
		}

		$statement = $this->connection->prepare('SELECT * FROM `'.$model->getTable().'`'.$parameters.' ORDER BY `'.$orderby.'` '.$order.$limitquery);

		$statement->execute($values);
		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		$results = $statement->fetchAll(PDO::FETCH_ASSOC);
		if(count($results) > 1 && $limit === null && $from === null) {
			throw new Exception('result ambiguity: expected unique identifier');
		} else if(count($results) == 1) {
			return $results[0];
		}

		return;
	}

	public function update(Model $model, $changes) {
		$this->ensureConnected();
		$model->ensureLoaded();

		Analog::debug('updating a '.get_class($model).' model, changes are '.print_r($changes, true));

		$values = array();
		$sets = '';
		foreach($changes as $row => $change) {
			if(!empty($values))
				$sets .= ', ';
			$sets .= '`'.$row.'` = ?';
			$values[] = $change;
		}
		$values[] = $model->getId();
		$query = $this->connection->prepare('UPDATE `'.$model->getTable().'` SET '.$sets.' WHERE `id` = ?');
		$query->execute($values);
		if($query->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		return $query->rowCount() == 1;
	}

	public function save(Model $model, $values) {
		$this->ensureConnected();
		$model->ensureUnloaded();

		Analog::debug('saving a '.get_class($model).' model, values are '.print_r($values, true));

		$keys = '';
		$valuenames = '';
		$contents = array();
		foreach($values as $key => $value) {
			if(!empty($contents)) {
				$keys .= ', ';
				$valuenames .= ', ';
			}
			$keys .= '`'.$key.'`';
			$valuenames .= '?';
			$contents[] = $value;
		}
		$query = $this->connection->prepare('INSERT INTO `'.$model->getTable().'` ('.$keys.') VALUES ('.$valuenames.')');
		$query->execute($contents);
		if($query->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $query->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		return $this->connection->lastInsertId();
	}

}
