<?php

/**
 * Defines method that must be implemented to allow a component act like a data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @package    Nette\Extras\DataGrid
 */
interface IDataGridColumn
{
	/**
	 * Is column orderable?
	 * @return bool
	 */
	function isOrderable();


	/**
	 * Gets header link (order signal)
	 * @param  string
	 * @return string
	 */
	function getOrderLink($dir = NULL);


	/**
	 * Has column filter box?
	 * @return bool
	 */
	function hasFilter();


	/**
	 * Returns column's filter.
	 * @return IDataGridColumnFilter|NULL
	 */
	function getFilter();


	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @return string
	 */
	function formatContent($value);


	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	function applyFilter($value);

}