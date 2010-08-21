<?php

namespace DataGrid\Renderers;

/**
 * Defines method that must implement data grid rendered.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @package    Nette\Extras\DataGrid
 */
interface IRenderer
{
	/**
	 * Provides complete data grid rendering.
	 * @param  DataGrid\Datagrid
	 * @return string
	 */
	function render(\DataGrid\DataGrid $dataGrid);

}