<?php

/**
 * Defines method that must be implemented to allow a component act like a data grid column's filter.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @package    Nette\Extras\DataGrid
 */
interface IDataGridColumnFilter
{
	/**
	 * Returns filter's form element.
	 * @return FormControl
	 */
	function getFormControl();


	/**
	 * Gets filter's value, if was filtered.
	 * @return string
	 */
	public function getValue();

}