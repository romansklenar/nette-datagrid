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
	 * Sqlite REGEXP implementation.
	 * @return bool
	 */
	static public function regexp($expr, $pattern) {
		return (bool) preg_match("/$pattern/i", $expr);
	}	
} 