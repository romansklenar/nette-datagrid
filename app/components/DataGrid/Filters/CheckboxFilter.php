<?php

namespace DataGrid\Filters;
use Nette, DataGrid;

require_once dirname(__FILE__) . '/../DataGridColumnFilter.php';

/**
 * Representation of data grid column checkbox filter.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class CheckboxFilter extends ColumnFilter
{
	/**
	 * Returns filter's form element.
	 * @return Nette\Forms\FormControl
	 */
	public function getFormControl()
	{
		if ($this->element instanceof Nette\Forms\FormControl) return $this->element;
		$element = new Nette\Forms\Checkbox($this->getName());

		return $this->element = $element;
	}
}