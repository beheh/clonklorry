<?php

namespace Lorry\Service;

use Resque;
use Lorry\Exception;

class JobService {

	/**
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;
	
	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	private $setup = false;

	public function ensureSetup() {
		if($this->setup) return;
		Resque::setBackend($this->config->get('job/dsn'));
		$this->setup = true;
	}
	
	public function submit($job, $args) {
		if(!is_array($args)) {
			throw new Exception('invalid arguments (not an array)');
		}
		$this->ensureSetup();
		$class_name = '\\Lorry\\Job\\'.$job.'Job';
		if(!class_exists($class_name)) {
			throw new Exception('unknown job');
		}
		if(!is_subclass_of($class_name, '\\Lorry\\Job')) {
			throw new Exception('job does not implement base class');			
		}
		$queue = call_user_func($class_name.'::getQueue');
		return Resque::enqueue($queue, $class_name, $args);
	}

}