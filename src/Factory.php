<?php

abstract class Factory {

	abstract static function getNamespace();
	abstract static function build($model);
}