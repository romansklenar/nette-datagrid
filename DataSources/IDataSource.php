<?php

namespace DataGrid\DataSources;

/**
 * An interface which provides main data logic for DataGrid
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
interface IDataSource extends \Countable, \IteratorAggregate
{
	/**#@+ ordering types */
	const ASCENDING = 'ASC';
	const DESCENDING = 'DESC';
	/**#@-*/


	/**#@+ filter operations */
	const EQUAL	= '=';
	const NOT_EQUAL = '!=';
	const GREATER = '>';
	const GREATER_OR_EQUAL = '>=';
	const LESS = '<';
	const LESS_OR_EQUAL = '<=';
	const LIKE = 'LIKE';
	const NOT_LIKE = 'NOT LIKE';
	const IS_NULL = 'IS NULL';
	const IS_NOT_NULL = 'IS NOT NULL';
	/**#@-*/


	/**#@+ filter chain types */
	const CHAIN_AND = 'AND';
	const CHAIN_OR	= 'OR';
	/**#@-*/


	/**
	 * Get list of columns available in datasource
	 * @return array
	 */
	function getColumns();

	/**
	 * Does datasource have column of given name?
	 *
	 * @return boolean
	 */
	function hasColumn($name);


	/**
	 * Return distinct values for a selectbox filter
	 * @param string Column name
	 * @return array
	 */
	function getFilterItems($column);


	/**
	 * Add filtering onto specified column
	 * @param string column name
	 * @param string filter
	 * @param string|array operation mode
	 * @param string chain type (if third argument is array)
	 * @throws \InvalidArgumentException
	 * @return IDataSource
	 */
	function filter($column, $operation = IDataSource::EQUAL, $value = NULL, $chainType = NULL);


	/**
	 * Adds ordering to specified column
	 * @param string column name
	 * @param string one of ordering types
	 * @throws \InvalidArgumentException
	 * @return IDataSource
	 */
	function sort($column, $order = IDataSource::ASCENDING);


	/**
	 * Reduce the result starting from $start to have $count rows
	 * @param int the number of results to obtain
	 * @param int the offset
	 * @throws \OutOfRangeException
	 * @return IDataSource
	 */
	function reduce($count, $start = 0);
}
