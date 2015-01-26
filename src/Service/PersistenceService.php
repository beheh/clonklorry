<?php

namespace Lorry\Service;

use Analog;
use PDO;
use Exception;
use InvalidArgumentException;
use PDOException;
use Lorry\Model;
use Aura\SqlQuery\QueryFactory;

class PersistenceService {

	/**
	 *
	 * @var ConfigService
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

	/**
	 *
	 * @var QueryFactory
	 */
	private $factory = null;

	public function ensureConnected() {
		if($this->connection !== null) {
			return true;
		}
		try {
			$dsn = $this->config->get('persistence/dsn');
			$this->connection = new PDO($dsn, $this->config->get('persistence/username'), $this->config->get('persistence/password'));
			$this->factory = new QueryFactory(strstr($dsn, ':', true));
		} catch(PDOException $ex) {
			// catch the pdo exception to prevent credential leaking
			throw new Exception('could not connect to database ('.$ex->getMessage().')');
		}
	}

	/**
	 * 
	 * @param Model $model
	 * @param array $pairs
	 * @param array $order
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function loadAll(Model $model, $pairs, $order, $offset, $limit) {
		$this->ensureConnected();
		$query = $this->factory->newSelect();

		$query->cols(array('*'));
		$query->from($model->getTable());

		foreach($pairs as $row => $value) {
			$query->where('`'.$row.'` = :'.$row);
			$query->bindValue($row, $value);
		}

		$query->orderBy($order);
		$query->offset($offset);
		$query->limit($limit);

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new Exception($errorinfo[1].': '.$errorinfo[2].' (sql error '.$errorinfo[0].' for query "'.$query.'")');
		}
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

		return $rows;
	}

	/**
	 * 
	 * @param Model $model
	 * @param array $pairs
	 * @param array $order
	 * @param int $offset
	 * @param int $limit
	 * @return Model
	 * @throws Exception
	 */
	public function load(Model $model, $pairs, $order, $offset, $limit) {
		$rows = $this->loadAll($model, $pairs, $order, $offset, $limit);
		if(count($rows) > 1 && $limit === null && $offset === null) {
			throw new Exception('result ambiguity: expected unique identifier');
		} else if(count($rows) == 1) {
			return $rows[0];
		}

		return null;
	}

	/**
	 * 
	 * @param Model $model
	 * @param array $changes
	 * @return bool
	 * @throws Exception
	 */
	public function update(Model $model, $changes) {
		$this->ensureConnected();
		$model->ensureLoaded();

		$query = $this->factory->newUpdate();
		$query->table($model->getTable());

		$query->cols($changes);

		$query->where('id = :id');
		$query->bindValue('id', $model->getId());

		Analog::debug('updating a '.get_class($model).' model, changes are '.print_r($changes, true));

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $query->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}

		return $statement->rowCount() == 1;
	}

	/**
	 * 
	 * @param Model $model
	 * @param array $values
	 * @return bool
	 * @throws Exception
	 */
	public function insert(Model $model, $values) {
		$this->ensureConnected();
		$model->ensureUnloaded();

		$query = $this->factory->newInsert();
		$query->into($model->getTable());

		$query->cols($values);

		Analog::debug('inserting a '.get_class($model).' model, values are '.print_r($values, true));

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $query->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		return $this->connection->lastInsertId();
	}

	/**
	 * 
	 * @param Model $model
	 * @return type
	 * @throws Exception
	 */
	public function delete(Model $model) {
		$this->ensureConnected();
		$model->ensureLoaded();

		$query = $this->factory->newDelete();
		$query->from($model->getTable());

		$query->where('id = :id');
		$query->bindValue('id', $model->getId());

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $query->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}

		return $statement->rowCount() == 1;
	}

}
