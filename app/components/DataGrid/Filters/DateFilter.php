<?php

require_once dirname(__FILE__) . '/TextFilter.php';



/**
 * Representation of data grid column date filter.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
class DateFilter extends TextFilter
{
	/**
	 * Returns filter's form element.
	 * @return FormControl
	 */
	public function getFormControl()
	{
		parent::getFormControl();
		$this->element->getControlPrototype()->addClass('datepicker');
		return $this->element;
	}
}