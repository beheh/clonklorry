<?php

namespace Lorry\Logger;

interface LoggerFactoryInterface {

	public function build($channel);
}
