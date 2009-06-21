<?php

/**
 * Sqlite user defined functions.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class Sqlite
{
	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}


	/**
	 * Sqlite REGEXP implementation.
	 * @return bool
	 */
	static public function regexp($expr, $pattern)
	{
		return (bool) preg_match("/$pattern/i", $expr);
	}
}