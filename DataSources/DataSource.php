<?php

namespace DataGrid\DataSources;

use Nette;

/**
 * Base class for all data sources
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
abstract class DataSource extends Nette\Object implements IDataSource
{
	/**
	 * Validate filter operation
	 * @param string $operation
	 * @return void
	 * @throws \InvalidStateException if operation is not valid
	 */
	protected function validateFilterOperation($operation)
	{
		static $types = array(
		  self::EQUAL,
		  self::NOT_EQUAL,
		  self::GREATER,
		  self::GREATER_OR_EQUAL,
		  self::LESS,
		  self::LESS_OR_EQUAL,
		  self::LIKE,
		  self::NOT_LIKE,
		  self::IS_NULL,
		  self::IS_NOT_NULL,
		);

		if (!in_array($operation, $types, TRUE)) {
			throw new \InvalidStateException('Invalid filter operation type.');
		}
	}
}