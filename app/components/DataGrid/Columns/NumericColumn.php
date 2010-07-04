<?php

namespace DataGrid\Columns;
use DataGrid;

/**
 * Representation of numeric data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class NumericColumn extends Column
{
	/** @var int */
	public $precision;


	/**
	 * Checkbox column constructor.
	 * @param  string  column's textual caption
	 * @param  string  number of digits after the decimal point
	 * @return void
	 */
	//public function __construct($caption = NULL, $precision = 2)
	public function __construct(DataGrid\DataGrid $dataGrid, $name, $caption = NULL, $precision = 2)
	{
		parent::__construct($dataGrid, $name, $caption);
		$this->precision = $precision;
	}


	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @param  \DibiRow|array
	 * @return string
	 */
	public function formatContent($value, $data = NULL)
	{
		if (is_array($this->replacement) && !empty($this->replacement)) {
			if (in_array($value, array_keys($this->replacement))) {
				$value = $this->replacement[$value];
			}
		}

		foreach ($this->formatCallback as $callback) {
			if (is_callable($callback)) {
				$value = call_user_func($callback, $value, $data);
			}
		}

		return round($value, $this->precision);
	}

	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) return;

		if ($value === 'NULL' || $value === 'NOT NULL') {
			//$cond[] = array("[$column] IS $value");
			$this->dataGrid->getDataSource()->filter($this->name, NULL, "IS $value");
		} else {
			$operator = '=';
			$v = str_replace(',', '.', $value);

			if (preg_match('/^(?<operator>\>|\>\=|\<|\<\=|\=|\<\>)?(?<value>[\.|\d]+)$/', $v, $matches)) {
				if (isset($matches['operator']) && !empty($matches['operator'])) {
					$operator = $matches['operator'];
				}
				$value = $matches['value'];
			}

			//$cond[] = array("[$column] $operator %f", $value);
			$this->dataGrid->getDataSource()->filter($this->name, (float) $value, $operator); //or skip converting?
		}

		//$datagrid = $this->dataGrid;
		//$datagrid->dataSource->where('%and', $cond);
	}
}