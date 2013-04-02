<?php

class Lorry_Autoloader {

	/**
	 * Registers Twig_Autoloader as an SPL autoloader.
	 */
	public static function register() {
		ini_set('unserialize_callback_func', 'spl_autoload_call');
		spl_autoload_register(array(new self, 'autoload'));
	}

	/**
	 * Handles autoloading of classes.
	 *
	 * @param string $class A class name.
	 */
	public static function autoload($class) {
		if(0 !== strpos($class, 'Lorry')) {
			return;
		}

		if(is_file($file = dirname(__FILE__).'/../'.str_replace(array('__', '_', "\0"), array('/_', '/', ''), $class).'.php')) {
			require $file;
		}
	}

}
