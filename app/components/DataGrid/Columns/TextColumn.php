<?php

require_once dirname(__FILE__) . '/../DataGridColumn.php';



/**
 * Representation of textual data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
class TextColumn extends DataGridColumn
{
	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @return string
	 */
	public function formatContent($value)
	{
		$value = htmlSpecialChars($value);
		if (is_array($this->replacement) && !empty($this->replacement)) {
			if (in_array($value, array_keys($this->replacement))) {
				$value = $this->replacement[$value];
			}
		}

		// translate & truncate
		if ($value instanceof Html) {
			$value->setText(String::truncate($this->dataGrid->translate($value->getText()), $this->maxLength));
			$value->title = $this->dataGrid->translate($value->title);
		} else {
			$value = String::truncate($this->dataGrid->translate($value), $this->maxLength);
		}

		foreach ($this->formatCallback as $callback) {
			if (is_callable($callback)) {
				$value = call_user_func($callback, $value);
			}
		}
		return $value;
	}


	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) return;

		$datagrid = $this->getDataGrid(TRUE);
		$column = $this->getName();
		$cond = array();

		if (strstr($value, '*')) {
			// rewrite asterix to regex usage (*str -> *str$, str* -> ^str*, st*r -> st.*r)
			$f = $value[0];
			$l = $value[strlen($value)-1];
			if ($f == '*' && $l == '*') $value = "^$value$";
			elseif ($f == '*' && $l != '$') $value = "$value$";
			elseif ($l == '*' && $f != '^') $value = "^$value";
			$value = str_replace('.*', '*', $value);
			$value = str_replace('*', '.*', $value);

			// NOTE: sqlite2 does not have REGEXP statement, you must register your own function
			$driver = $datagrid->dataSource->getConnection()->getConfig('driver');
			if ($driver == 'sqlite' && (int) sqlite_libversion() == 2) {
				$cond[] = array("REGEXP($column, '$value')");
			} else {
				$cond[] = array("[$column] REGEXP '$value'");
			}

		} else {
			$cond[] = array("[$column] LIKE '%$value%'");
		}

		$datagrid->dataSource->where('%and', $cond);
	}
}