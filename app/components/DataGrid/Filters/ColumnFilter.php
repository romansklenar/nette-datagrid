<?php

namespace DataGrid\Filters;
use Nette, DataGrid;

/**
 * Base class that implements the basic common functionality to data grid column's filters.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
abstract class ColumnFilter extends Nette\Component implements IColumnFilter
{
	/** @var Nette\Forms\FormControl  form element */
	protected $element;

	/** @var string  value of filter (if was filtered) */
	protected $value;


	public function __construct()
	{
		parent::__construct();
	}



	/********************* interface DataGrid\Filters\IColumnFilter *********************/



	/**
	 * Returns filter's form element.
	 * @return Nette\Forms\FormControl
	 */
	public function getFormControl()
	{
	}


	/**
	 * Gets filter's value, if was filtered.
	 * @return string
	 */
	public function getValue()
	{
		$dataGrid = $this->lookup('DataGrid\DataGrid', TRUE);

		// set value if was data grid filtered yet
		parse_str($dataGrid->filters, $list);
		foreach ($list as $key => $value) {
			if ($key == $this->getName()) {
				$this->setValue($value);
				break;
			}
		}
		return $this->value;
	}


	/**
	 * Sets filter's value.
	 * @param string
	 * @return void
	 */
	public function setValue($value)
	{
		$this->getFormControl()->setDefaultValue($value);
		$this->value = $value;
	}
}