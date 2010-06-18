<?php

require_once dirname(__FILE__) . '/TextColumn.php';



/**
 * Representation of image data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class ImageColumn extends TextColumn
{
	/**
	 * Checkbox column constructor.
	 * @param  string  column's textual caption
	 * @return void
	 */
	public function __construct($caption = NULL)
	{
		throw new NotImplementedException("Class was not implemented yet.");
		parent::__construct($caption);
	}
}