<?php

namespace Lorry\Service;

use Lorry\Service;
use Lorry\Logger\LoggerFactoryInterface;
use Resque\Resque;
use Predis\Client;
use Lorry\Exception;

class JobService extends Service {

	/**
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;
	
	public function __construct(LoggerFactoryInterface $loggerFactory, ConfigService $config) {
		parent::__construct($loggerFactory);
		$this->config = $config;
	}


	/**
	 *
	 * @var \Resque\Resque;
	 */
	private $resque;

	public function ensureConnected() {
		if($this->resque) return;
		$this->resque = new Resque(new Client($this->config->get('job/dsn')));
	}
	
	public function build($job_name) {
		$class_name = '\\Lorry\\Job\\'.$job_name.'Job';
		if(!class_exists($class_name)) {
			throw new Exception('unknown job');
		}
		if(!is_subclass_of($class_name, '\\Lorry\\Job')) {
			throw new Exception('job does not implement base class');			
		}
		return $class_name;
	}
	
	public function getQueue($job_class) {
		return call_user_func($job_class.'::getQueue');
	}
	
	public function submit($job_name, $args) {
		if(!is_array($args)) {
			throw new Exception('invalid arguments (not an array)');
		}
		$this->ensureConnected();
		$class_name = $this->build($job_name);
		$result = $this->resque->enqueue($this->getQueue($class_name), $class_name, $args);
		$this->logger->notice('queuing "'.$job_name.'" (queue "'.$this->getQueue($class_name).'"): result is '.print_r($result, true));
		return $result;
	}
	
	public function remove($job_name, $args) {
		if(!is_array($args)) {
			throw new Exception('invalid arguments (not an array)');
		}
		$this->ensureConnected();
		//@todo implement dequeuing
		$class_name = $this->build($job_name);
		$filter = array($class_name => $args);
		//$result = $this->resque->dequeue($this->getQueue($class_name), $filter);
		//$this->logger->notice('dequeuing "'.$job_name.'" (queue "'.$this->getQueue($class_name).'"): result is '.print_r($result, true));
		//return $result;
		return false;
	}

}
