<?php

namespace Lorry;

use \Lorry\Service\PersistenceService;

interface ModelInterface {
	public function setPersistence(PersistenceService $persistence);
}