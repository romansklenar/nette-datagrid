<?php

require_once dirname(__FILE__) . '/../DataGridColumn.php';



/**
 * Representation of textual data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář
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
		$value = String::truncate((string) $value, $this->maxLength);		
	
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
		$cond[] = array("[$column] LIKE '%$value%'");
		$datagrid->dataSource->where('%and', $cond);
	}
}