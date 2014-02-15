<?php

namespace Lorry;

use Lorry\Service\PersistenceService;

interface ModelInterface {
	public function setPersistenceService(PersistenceService $persistence);
}