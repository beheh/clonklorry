<?php

namespace Lorry\Service;

use Lorry\Service;
use Lorry\Logger\LoggerFactoryInterface;
use PDO;
use RuntimeException;
use InvalidArgumentException;
use PDOException;
use Lorry\Model;
use Aura\SqlQuery\QueryFactory;
use Interop\Container\ContainerInterface;

class PersistenceService extends Service {

	/**
	 *
	 * @var ConfigService
	 */
	protected $config;

	/**
	 *
	 * @var \Interop\Container\ContainerInterface
	 */
	protected $container;

	public function __construct(LoggerFactoryInterface $loggerFactory, ConfigService $config, ContainerInterface $container) {
		parent::__construct($loggerFactory);
		$this->config = $config;
		$this->container = $container;
	}

	public function getLogChannel() {
		return 'persistence';
	}

	protected $cache;

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

	private $hasFailed = false;

	public function hasFailed() {
		return $this->hasFailed;
	}

	public function ensureConnected() {
		if($this->connection !== null) {
			return true;
		}
		try {
			$dsn = $this->config->get('persistence/dsn');
			$this->connection = new PDO($dsn, $this->config->get('persistence/username'), $this->config->get('persistence/password'));
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->factory = new QueryFactory(strstr($dsn, ':', true));
		} catch(PDOException $ex) {
			$this->hasFailed = true;
			// catch the pdo exception to prevent credential leaking (either logs or debug frontend)
			throw new RuntimeException('could not connect to database ('.$ex->getMessage().')');
		}
	}

	public function build($model) {
		$model = '\\Lorry\\Model\\'.$model;
		if(!class_exists($model)) {
			throw new RuntimeException('unknown model "'.$model.'"');
		}
		return new $model($this->config, $this);
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

		foreach($pairs as $row => $parameter) {
			$operator = '=';
			if(is_array($parameter)) {
				$operator = $parameter[0];
				$value = $parameter[1];
			} else {
				$value = $parameter;
			}
			if($value === null) {
				switch($operator) {
					case '=':
						$operator = 'IS';
						break;
					case '!=':
						$operator = 'IS NOT';
						break;
				}
				$query->where($row.' '.$operator.' null');
			} else {
				$query->where($row.' '.$operator.' :'.$row);
				$query->bindValue($row, $value);
			}
		}

		$query->orderBy($order);
		$query->offset($offset);
		$query->limit($limit);

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new RuntimeException($errorinfo[1].': '.$errorinfo[2].' (sql error '.$errorinfo[0].' for query "'.$query.'")');
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
			throw new RuntimeException('result ambiguity: expected unique identifier');
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

		$this->logger->info('updating a '.get_class($model).' model, changes are '.print_r($changes, true));

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new RuntimeException('#'.$errorinfo[1].': '.$errorinfo[2]);
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

		$this->logger->info('inserting a '.get_class($model).' model, values are '.print_r($values, true));

		$statement = $this->connection->prepare($query->__toString());
		$statement->execute($query->getBindValues());

		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new RuntimeException('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		return $this->connection->lastInsertId();
	}

	/**
	 * 
	 * @param Model $model
	 * @return bool
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
			$errorinfo = $statement->errorInfo();
			throw new RuntimeException('#'.$errorinfo[1].': '.$errorinfo[2]);
		}

		return $statement->rowCount() == 1;
	}

}
