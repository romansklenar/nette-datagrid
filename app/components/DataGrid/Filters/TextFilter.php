<?php

namespace DataGrid\Filters;
use Nette;

require_once dirname(__FILE__) . '/../DataGridColumnFilter.php';

/**
 * Representation of data grid column textual filter.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class TextFilter extends ColumnFilter
{
	/**
	 * Returns filter's form element.
	 * @return Nette\Forms\FormControl
	 */
	public function getFormControl()
	{
		if ($this->element instanceof Nette\Forms\FormControl) return $this->element;

		$this->element = new Nette\Forms\TextInput($this->getName(), 5);
		return $this->element;
	}
}