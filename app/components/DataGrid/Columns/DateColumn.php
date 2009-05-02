<?php

require_once dirname(__FILE__) . '/TextColumn.php';



/**
 * Representation of date data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
class DateColumn extends TextColumn
{
	/** @var string */
	public $format;
	
	
	/**
	 * Date column constructor.
	 * @param  string  column's textual caption
	 * @param  string  date format supported by PHP strftime()
	 * @return void
	 */
	public function __construct($caption = NULL, $format = '%x')
	{
		parent::__construct($caption);
		$this->format = $format;
		$this->getHeaderPrototype()->style('width: 80px');
	}
	
	
	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	public function formatContent($value)
	{
		if ($value == NULL || empty($value)) return 'N/A';
		$value = parent::formatContent($value);
		
		$value = is_numeric($value) ? (int) $value : ($value instanceof DateTime ? $value->format('U') : strtotime($value));
		return strftime($this->format, $value);
	}
	
		
	/**
	 * Applies filtering on dataset.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) return;
		
		$datagrid = $this->getDataGrid(TRUE);		
		$column = $this->getName();
		$cond = array();
		$cond[] = array("[$column] = %t", $value);
		$datagrid->dataSource->where('%and', $cond);
	}
}