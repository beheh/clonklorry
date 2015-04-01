<?php

namespace Lorry\Service;

use Lorry\Service;

class RequestCacheService extends Service
{
    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param string $model
     * @param Model $object
     */
    public function put($model, \Lorry\Model $object)
    {
        if (!$object->isLoaded() || $object->modified()) {
            return;
        }
        if (!isset($this->cache[$model])) {
            $this->cache[$model] = array();
        }
        $this->cache[$model][$object->getId()] = $object;
    }

    /**
     * @param string $model
     * @param int $id
     * @return Model|bool
     */
    public function getById($model, $id)
    {
        if (!isset($this->cache[$model])) {
            return;
        }
        if (isset($this->cache[$model][$id])) {
            return $this->cache[$model][$id];
        }
        return false;
    }
}
