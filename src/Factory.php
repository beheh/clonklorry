<?php

abstract class Factory
{

    abstract public static function getNamespace();

    abstract public static function build($model);
}
