<?php

require_once dirname(__FILE__) . '/../DataGridColumn.php';



/**
 * Representation of data grid action column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
class ActionColumn extends DataGridColumn
{
	/**
	 * Action column constructor.
	 * @param  string  column's textual caption
	 * @return void
	 */
	public function __construct($caption = 'Actions')
	{
		parent::__construct($caption);
		$this->orderable = FALSE;
	}
	
	
	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @return string
	 */
	public function formatContent($value)
	{
		trigger_error('ActionColumn cannot be formated.', E_USER_WARNING);
		// TODO: or throw exception?
		//throw new InvalidStateException("ActionColumn cannot be formated.");
		return $value;
	}
	
	
	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		trigger_error('ActionColumn cannot be filtered.', E_USER_WARNING);
		// TODO: or throw exception?
		//throw new InvalidStateException("ActionColumn cannot be filtered.");
		return;
	}	
}