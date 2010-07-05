<?php

namespace DataGrid\DataSources\Doctrine;

use Nette, Doctrine, DataGrid,
	Doctrine\ORM\Query\Expr,
	DataGrid\DataSources\IDataSource,
	DataGrid\DataSources\DataSource;

/**
 * Base class for Doctrine2 based data sources
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
abstract class Mapped extends DataSource
{
	/** @var array Column aliases to raw resultset columns mapping */
	protected $mapping = array();

	/**
	 * Set columns mapping
	 * @param array
	 * @return void
	 */
	public function setMapping(array $mapping)
	{
		$this->mapping = $mapping;
	}

	/**
	 * Is column with given name valid?
	 * @return bool
	 */
	public function isValid($column)
	{
		return in_array($column, $this->mapping);
	}
}
