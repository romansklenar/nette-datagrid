<?php

namespace DataGrid\DataSources\Doctrine;

class WildcardHelper
{
	/**
	 * Format given value for LIKE statement
	 *
	 * @param string $value
	 * @return string
	 */
	public static function formatLikeStatementWildcards($value)
	{
		// Escape wildcard character used in PDO
		$value = str_replace('%', '\\%', $value);

		// Replace asterisks
		$value = \Nette\String::replace($value, '~(?!\\\\)(.?)\\*~', '\\1%');

		// Replace escaped asterisks
		return str_replace('\\*', '*', $value); 
	}
}