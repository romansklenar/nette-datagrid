<?php

require_once dirname(__FILE__) . '/../DataGridColumnFilter.php';



/**
 * Representation of data grid column selectbox filter.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
class SelectboxFilter extends DataGridColumnFilter
{
	/** @var array  asociative array of items in selectbox */
	protected $generatedItems;
	
	/** @var array  asociative array of items in selectbox */
	protected $items;
	
	/** @var bool */
	protected $translateItems;
	
	/** @var bool */
	protected $skipFirst;
	
	
	/**
	 * Selectbox filter constructor.
	 * @param  array   items from which to choose
	 * @param  int     skip first items value from validation?
	 * @param  bool    translate all items in selectbox?
	 * @return void
	 */
	public function __construct(array $items = NULL, $skipFirst = NULL, $translateItems = TRUE)
	{
		$this->items = $items;
		$this->skipFirst = $skipFirst;
		$this->translateItems = $translateItems;
		parent::__construct();
	}
	
	
	/**
	 * Generates selectbox items.
	 * @return array
	 */
	public function generateItems()
	{
		// NOTE: don't generate if was items given in constructor
		if (is_array($this->items)) return;
		
		$dataGrid = $this->lookup('DataGrid', TRUE);
		
		$columnName = $this->getName();
		$fluent = $dataGrid->dataSource->toFluent();
		$fluent->clause('select', TRUE);
		$fluent->select();
		$fluent->distinct($columnName);
		
		$cond = array();
		$cond[] = array("[$columnName] NOT LIKE %s", '');
		
		$fluent->where('%and', $cond)->orderBy($columnName);
		$items = $fluent->fetchPairs($columnName, $columnName);
		
		$this->generatedItems = array_merge(array('?' => '?'), $items);
		$this->skipFirst = TRUE;
		
		// if was data grid already filtered by this filter don't update with filtred items (keep full list)
		if (empty($this->element->value)) {
			$this->element->setItems($this->generatedItems);
		}
		
		return $this->items;
	}


	/**
	 * Returns filter's form element.
	 * @return FormControl
	 */
	public function getFormControl()
	{
		if ($this->element instanceof FormControl) return $this->element;
		$this->element = new SelectBox($this->getName(), $this->items);
		
		// prepare items
		if ($this->items === NULL) {
			$this->generateItems();
		}
		
		// skip first item?
		if ($this->skipFirst) {
			$this->element->skipFirst();
		}
		
		// translate items?
		if (!$this->translateItems) {
			$this->element->setTranslator(NULL);
		}
		
		return $this->element;
	}
	
	
	/**
	 * Translate all items in selectbox?
	 * @param  bool
	 * @return FormControl  provides a fluent interface
	 */
	public function translateItems($translate)
	{
		$this->translateItems = (bool) $translate;
		return $this->getFormControl();
	}
}