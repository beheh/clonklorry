<?php

namespace Lorry\Service;

use Lorry\Logger\LoggerFactoryInterface;
use Resque\Resque;
use Predis\Client;
use Lorry\Exception\Exception;

class JobService extends AbstractService
{
    /**
     *
     * @var \Resque\Resque;
     */
    private $resque;

    public function __construct(LoggerFactoryInterface $loggerFactory,
        Client $client)
    {
        parent::__construct($loggerFactory);
        $this->resque = new Resque($client);
    }

    public function build($job_name)
    {
        $class_name = '\\Lorry\\Job\\'.$job_name.'Job';
        if (!class_exists($class_name)) {
            throw new Exception('unknown job');
        }
        if (!is_subclass_of($class_name, '\\Lorry\\Job')) {
            throw new Exception('job does not implement base class');
        }
        return $class_name;
    }

    public function getQueue($job_class)
    {
        return call_user_func($job_class.'::getQueue');
    }

    public function submit($job_name, $args)
    {
        if (!is_array($args)) {
            throw new Exception('invalid arguments (not an array)');
        }
        $class_name = $this->build($job_name);
        $this->logger->notice('queuing "'.$job_name.'" in queue "'.$this->getQueue($class_name).'"');
        $result = false;
        try {
            $result = $this->resque->enqueue($this->getQueue($class_name),
                $class_name, $args);
        } catch (\Exception $ex) {
            $this->logger->error($ex);
            return false;
        }
        return $result;
    }

    public function remove($job_name, $args)
    {
        if (!is_array($args)) {
            throw new Exception('invalid arguments (not an array)');
        }
        //@todo implement dequeuing
        $class_name = $this->build($job_name);
        $filter = array($class_name => $args);
        //$result = $this->resque->dequeue($this->getQueue($class_name), $filter);
        //$this->logger->notice('dequeuing "'.$job_name.'" (queue "'.$this->getQueue($class_name).'"): result is '.print_r($result, true));
        //return $result;
        return false;
    }
}
