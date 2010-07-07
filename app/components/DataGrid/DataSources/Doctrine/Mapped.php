<?php

namespace DataGrid\DataSources\Doctrine;

use Nette, Doctrine, DataGrid,
	Doctrine\ORM\Query\Expr,
	DataGrid\DataSources\IDataSource,
	DataGrid\DataSources\DataSource;

/**
 * Base class for Doctrine2 based data sources
 * 
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
	 * Does datasource have column of given name?
	 *
	 * @return boolean
	 */
	public function hasColumn($name)
	{
		return \array_key_exists($name, $this->mapping);
	}

	
	/**
	 * Get aliased column name list
	 *
	 * @return array
	 */
	public function getColumns()
	{
		return array_keys($this->mapping);
	}


	/**
	 * Get sample record from data source
	 *
	 * @return array
	 */
	protected function getDataSample()
	{
		static $cache = NULL;
		if ($cache === NULL) {
			$ds = clone $this;
			$cache = $ds->reduce(1)->first();
		}
		return $cache;
	}

	
	/**
	 * Generate simple mapping with column name uniqueness in mind
	 *
	 * @return void
	 */
	protected function generateMapping()
	{
		foreach ($this->getDataSample() as $column => $value) {
			if (isset($this->mapping[$column])) {
				throw new \InvalidStateException('Unable to generate the mapping because of ambiguous column names.');
			}
			$this->mapping[$column] = strtr($column, '_', '.');
		}
	}
}
