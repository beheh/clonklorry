<?php

require __DIR__.'/../vendor/autoload.php';

\Analog::handler(\Analog\Handler\File::init(__DIR__.'/../app/logs/lorry.log'));
