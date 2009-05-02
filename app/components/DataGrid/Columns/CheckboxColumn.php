<?php

require_once dirname(__FILE__) . '/NumericColumn.php';



/**
 * Representation of checkbox data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
class CheckboxColumn extends NumericColumn
{
	/**
	 * Checkbox column constructor.
	 * @param  string  column's textual caption
	 * @return void
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption, 0);
		$this->getCellPrototype()->style('text-align: center');
	}
	
	
	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @return string
	 */
	public function formatContent($value)
	{		
		$checkbox = Html::el('input')->type('checkbox')->disabled('disabled');
		if ($value) $checkbox->checked = TRUE;
		return (string) $checkbox;
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
		$value = (int)(bool)$value;
		$cond = array();
		$cond[] = array("[$column] " . ($value ? ">" : "=") . " %b", FALSE);		
		$datagrid->dataSource->where('%and', $cond);
	}
}