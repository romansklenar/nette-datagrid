<?php

namespace DataGrid;

/**
 * Defines method that must be implemented to allow a component act like a data grid action.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @package    Nette\Extras\DataGrid
 */
interface IAction
{
	/**
	 * Gets action element template.
	 * @return Nette\Web\Html
	 */
	function getHtml();

}