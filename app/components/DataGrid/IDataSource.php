<?php

namespace DataGrid;

/**
 * An interface which provides main data logic for DataGrid
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
interface IDataSource extends \Countable, \IteratorAggregate
{
	/**#@+ ordering types */
	const ASCENDING		= 1;
	const DESCENDING	= 2;
	/**#@-*/

	/**#@+ filter operations */
	const EQUAL				= '=';
	const NOT_EQUAL			= '!=';
	const GREATER			= '>';
	const GREATER_OR_EQUAL	= '>=';
	const SMALLER			= '<';
	const SMALLER_OR_EQUAL	= '<=';
	const LIKE				= 'LIKE';
	const NOT_LIKE			= 'NOT LIKE';
	/**#@-*/

	/**
	 * Select columns
	 * @param string|array columns to be selected
	 * @throws \InvalidArgumentException
	 */
	function select($columns);

	/**
	 * Add filtering onto specified column
	 * @param string column name
	 * @param string filter
	 * @param int operation mode
	 * @throws \InvalidArgumentException
	 */
	function filter($column, $value, $operation = IDataSource::EQUAL);

	/**
	 * Adds ordering to specified column
	 * @param string column name
	 * @param int one of IDataSource::ASCENDING, IDataSource::DESCENDING
	 * @throws \InvalidArgumentException
	 */
	function sort($column, $order = IDataSource::ASCENDING);

	/**
	 * Reduce the result starting from $start to have $count rows
	 * @param int the number of results to obtain
	 * @param int the offset
	 * @throws \OutOfRangeException
	 */
	function reduce($count, $start = 0);

	//function getIterator()

	//function count()
}