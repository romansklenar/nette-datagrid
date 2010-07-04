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
	const ASCENDING		= 'ASC';
	const DESCENDING	= 'DESC';
	/**#@-*/

	/**#@+ select modes */
	const NORMAL		= 'NORMAL';
	const DISTINCT		= 'DISTINCT';
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
	const IS_NULL			= 'IS NULL';
	const IS_NOT_NULL		= 'IS NOT NULL';
	/**#@-*/

	/**#@+ filter chain types */
	const CHAIN_AND		= 'AND';
	const CHAIN_OR		= 'OR';
	/**#@-*/

	/**
	 * Select columns
	 * @param string|array columns to be selected
	 * @throws \InvalidArgumentException
	 */
	function select($columns, $mode = IDataSource::NORMAL);

	/**
	 * Add filtering onto specified column
	 * @param string column name
	 * @param string filter
	 * @param string|array operation mode
	 * @param string chain type (if third argument is array)
	 * @throws \InvalidArgumentException
	 */
	function filter($column, $value = NULL, $operation = IDataSource::EQUAL, $chainType = NULL);

	/**
	 * Adds ordering to specified column
	 * @param string column name
	 * @param string one of ordering types
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