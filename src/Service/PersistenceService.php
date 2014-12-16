<?php

namespace Lorry\Service;

use Analog;
use PDO;
use Exception;
use InvalidArgumentException;
use PDOException;
use Lorry\Model;

class PersistenceService {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
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
			$this->connection = new PDO($this->config->get('persistence/dsn'), $this->config->get('persistence/username'), $this->config->get('persistence/password'));
		} catch(PDOException $ex) {
			// catch the pdo exception to prevent credential leaking
			throw new Exception('could not connect to database ('.$ex->getMessage().')');
		}
	}

	/**
	 * 
	 * @param \Lorry\Model $model
	 * @param array $pairs
	 * @param bool $descending
	 * @param int $from
	 * @param int $limit
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function loadAll(Model $model, $pairs, $order, $from, $limit) {
		$this->ensureConnected();

		$parameters = '';
		$values = array();
		$where = false;
		foreach($pairs as $row => $value) {
			if(!$where) {
				$parameters .= ' WHERE ';
				$where = true;
			} else {
				$parameters .= ' AND ';
			}
			if(is_array($value)) {
				if(count($value) !== 2) {
					throw new InvalidArgumentException('invalid contraint value, expected array of size 2 (for example array("!=", "value"))');
				}
				$allowed = array('>', '>=', '=', '!=', '<=', '<');
				if(!in_array($value[0], $allowed)) {
					throw new InvalidArgumentException('invalid query modifier, must be one of '.implode(', ', $allowed));
				}
				if($value[1] === null) {
					switch($value[0]) {
						case '=':
							$parameters .= '`'.$row.'` IS NULL';
							break;
						case '!=':
							$parameters .= '`'.$row.'` IS NOT NULL';
							break;
						default:
							throw new InvalidArgumentException('invalid query modifier, null can only be equal or unequal');
							break;
					}
				} else {
					$parameters .= '`'.$row.'` '.$value[0].' ?';
					$values[] = $value[1];
				}
			} else {
				if($value === null) {
					$parameters .= '`'.$row.'` IS NULL';
				} else {
					$parameters .= '`'.$row.'` = ?';
					$values[] = $value;
				}
			}
		}

		$limitquery = '';
		if($limit !== null) {
			$limitquery = ($from === null) ? ' LIMIT '.intval($limit) : ' LIMIT '.intval($from).', '.intval($limit);
		}

		$orderquery = ' ORDER BY ';
		$i = 0;
		foreach($order as $row => $descending) {
			if($i > 0) {
				$orderquery .= ', ';
			}
			$orderquery .= '`'.$row.'` ';
			$orderquery .= $descending ? 'DESC' : 'ASC';
			$i++;
		}

		$query = 'SELECT * FROM `'.$model->getTable().'`'.$parameters.$orderquery.$limitquery;
		$statement = $this->connection->prepare($query);
		$statement->execute($values);
		if($statement->errorCode() != PDO::ERR_NONE) {
			$errorinfo = $statement->errorInfo();
			throw new Exception($errorinfo[1].': '.$errorinfo[2].' (sql error '.$errorinfo[0].' for query "'.$query.'")');
		}
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

		return $rows;
	}

	/**
	 * 
	 * @param \Lorry\Model $model
	 * @param array $pairs
	 * @param array $order
	 * @param int $from
	 * @param int $limit
	 * @return \Lorry\Model
	 * @throws Exception
	 */
	public function load(Model $model, $pairs, $order, $from, $limit) {
		$rows = $this->loadAll($model, $pairs, $order, $from, $limit);
		if(count($rows) > 1 && $limit === null && $from === null) {
			throw new Exception('result ambiguity: expected unique identifier');
		} else if(count($rows) == 1) {
			return $rows[0];
		}

		return null;
	}

	/**
	 * 
	 * @param \Lorry\Model $model
	 * @param array $changes
	 * @return bool
	 * @throws Exception
	 */
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
			$errorinfo = $query->errorInfo();
			throw new Exception('#'.$errorinfo[1].': '.$errorinfo[2]);
		}
		return $query->rowCount() == 1;
	}

	/**
	 * 
	 * @param \Lorry\Model $model
	 * @param array $values
	 * @return bool
	 * @throws Exception
	 */
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
