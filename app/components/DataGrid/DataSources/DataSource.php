<?php

namespace DataGrid\DataSources;

use Nette;

/**
 * Base class for all data sources
 */
abstract class DataSource extends Nette\Object implements IDataSource
{

	protected function validateFilterOperation($operation)
	{
		static $types = array(
		  self::EQUAL,
		  self::NOT_EQUAL,
		  self::GREATER,
		  self::GREATER_OR_EQUAL,
		  self::SMALLER,
		  self::SMALLER_OR_EQUAL,
		  self::LIKE,
		  self::NOT_LIKE,
		  self::IS_NULL,
		  self::IS_NOT_NULL,
		);

		if (!in_array($operation, $types)) {
			throw new \InvalidArgumentException('Invalid filter operation type.');
		}
	}

}