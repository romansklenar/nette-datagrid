<?php

namespace DataGrid\DataSources\PHPArray;
use Nette, DataGrid;

/**
 * An array data source for DataGrid
 * @author Michael Moravec
 */
class PHPArray extends DataGrid\DataSources\DataSource
{
	/** @var array */
	private $items;
	
	/** @var array */
	private $source;

	/** @var array */
	private $filters;

	/** @var array */
	private $sorting;

	/** @var array */
	private $reducing;

	public function __construct(array $items)
	{
		if (empty($items)) {
			throw new \InvalidArgumentException('Empty array given');
		}

		$this->items = $this->source = $items;
	}

	public function filter($column, $operation = self::EQUAL, $value = NULL, $chainType = NULL)
	{
		throw new \NotImplementedException;
	}

	public function sort($column, $order = self::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			throw new \InvalidArgumentException;
		}
		usort($this->items, function ($a, $b) use ($column, $order) {
			return $order === DataGrid\DataSources\IDataSource::DESCENDING ? -strcmp($a[$column], $b[$column]) : strcmp($a[$column], $b[$column]);
		});
	}

	public function reduce($count, $start = 0)
	{
		$this->items = array_slice($this->items, $start, $count);
	}

	public function getColumns()
	{
		return array_keys(reset($this->source));
	}

	public function hasColumn($name)
	{
		return array_key_exists($name, reset($this->source));
	}

	public function getFilterItems($column)
	{
		throw new \NotImplementedException;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	public function count()
	{
		return count($this->items);
	}
}