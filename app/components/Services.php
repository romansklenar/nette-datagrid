<?php


class Services extends Object
{
	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}
	
	/**
	 * Services initializator
	 * @return void
	 */
	public static function initialize()
	{
		return;
	}
}