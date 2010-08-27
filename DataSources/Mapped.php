<?php

namespace DataGrid\DataSources;

/**
 * Base class for Doctrine2 based data sources
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
abstract class Mapped extends DataSource
{
	/**
	 * @var array Alias to column mapping
	 */
	protected $mapping = array();

	/**
	 * Get columns mapping
	 * @param array
	 * @return void
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

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
	 * @return boolean
	 */
	public function hasColumn($name)
	{
		return array_key_exists($name, $this->mapping);
	}

	/**
	 * Get list of column aliases
	 * @return array
	 */
	public function getColumns()
	{
		return array_keys($this->mapping);
	}
}