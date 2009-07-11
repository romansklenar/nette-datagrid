<?php

require_once dirname(__FILE__) . '/IDataGridColumn.php';



/**
 * Base class that implements the basic common functionality to data grid columns.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
 */
abstract class DataGridColumn extends Component implements IDataGridColumn
{
	/** @var Html  table header element template */
	protected $header;

	/** @var Html  table cell element template */
	protected $cell;

	/** @var string */
	protected $caption;

	/** @var int */
	protected $maxLength = 100;

	/** @var array  of arrays('pattern' => 'replacement') */
	public $replacement;

	/** @var array  of callback functions */
	public $formatCallback = array();

	/** @var bool */
	public $orderable = TRUE;

	/** @var string */
	static public $ajaxClass = 'datagrid-ajax';


	/**
	 * Data grid column constructor.
	 * @param  string  textual caption of column
	 * @param  int     maximum number of dislayed characters
	 */
	public function __construct($caption = NULL, $maxLength = NULL)
	{
		parent::__construct();
		$this->header = Html::el();
		$this->cell = Html::el();
		$this->caption = $caption;
		if ($maxLength !== NULL) $this->maxLength = $maxLength;
		$this->monitor('DataGrid');
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($dataGrid)
	{
		if ($dataGrid instanceof DataGrid) {
			$this->setParent($dataGrid->getComponent('columns', TRUE));

			if ($this->caption === NULL) {
				$this->caption = $this->getName();
			}
		}
	}


	/**
	 * Returns DataGrid.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return DataGrid
	 */
	public function getDataGrid($need = TRUE)
	{
		return $this->lookup('DataGrid', $need);
	}



	/********************* Html objects getters *********************/



	/**
	 * Returns headers's HTML element template.
	 * @return Html
	 */
	public function getHeaderPrototype()
	{
		return $this->header;
	}


	/**
	 * Returns table's cell HTML element template.
	 * @return Html
	 */
	public function getCellPrototype()
	{
		return $this->cell;
	}


	/**
	 * Setter / property method.
	 * @return string
	 */
	public function getCaption()
	{
		if ($this->caption instanceof Html && $this->caption->title) {
			return $this->caption->title($this->getDataGrid(TRUE)->translate($this->caption->title));
		} else {
			return $this->getDataGrid(TRUE)->translate($this->caption);
		}
	}



	/********************* interface \IDataGridColumn *********************/



	/**
	 * Is column orderable?
	 * @return bool
	 */
	public function isOrderable()
	{
		return $this->orderable;
	}


	/**
	 * Gets header link (order signal)
	 * @param  string  direction of sorting (a|d|NULL)
	 * @return string
	 */
	public function getOrderLink($dir = NULL)
	{
		return $this->getDataGrid(TRUE)->link('order', array('by' => $this->getName(), 'dir' => $dir));
	}


	/**
	 * Has column filter box?
	 * @return bool
	 */
	public function hasFilter()
	{
		return $this->getDataGrid(TRUE)->getComponent('filters', TRUE)->getComponent($this->getName(), FALSE) instanceof IDataGridColumnFilter;
	}


	/**
	 * Returns column's filter.
	 * @param  bool   throw exception if component doesn't exist?
	 * @return IDataGridColumnFilter|NULL
	 */
	public function getFilter($need = TRUE)
	{
		return $this->getDataGrid(TRUE)->getComponent('filters', TRUE)->getComponent($this->getName(), $need);
	}


	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @return string
	 */
	public function formatContent($value)
	{
		trigger_error('DataGridColumn::formatContent should not be called; Overload this method by your implementation in descendant.', E_USER_WARNING);
		return (string) $value;
	}


	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		trigger_error('DataGridColumn::applyFilter should not be called; Overload this method by your implementation in descendant.', E_USER_WARNING);
		return;
	}


	
	/********************* Default sorting and filtering *********************/



	/**
	 * Adds default sorting to data grid.
	 * @param string
	 * @return DataGridColumn  provides a fluent interface
	 */
	public function addDefaultSorting($order = 'ASC')
	{
		$orders = array('ASC', 'DESC', 'asc', 'desc', 'A', 'D', 'a', 'd');
		if (!in_array($order, $orders)) {
			throw new InvalidArgumentException("Order must be in '" . implode(', ', $orders) . "', '$order' given.");
		}

		parse_str($this->getDataGrid()->defaultOrder, $list);
		$list[$this->getName()] = strtolower($order[0]);
		$this->getDataGrid()->defaultOrder = http_build_query($list, '', '&');
		
		return $this;
	}
	
	
	/**
	 * Adds default filtering to data grid.
	 * @param string
	 * @return DataGridColumn  provides a fluent interface
	 */
	public function addDefaultFiltering($value)
	{
		parse_str($this->getDataGrid()->defaultFilters, $list);
		$list[$this->getName()] = $value;
		$this->getDataGrid()->defaultFilters = http_build_query($list, '', '&');
		
		return $this;
	}
	
	
	/**
	 * Removes data grid's default sorting.
	 * @return DataGridColumn  provides a fluent interface
	 */
	public function removeDefaultSorting()
	{
		parse_str($this->getDataGrid()->defaultOrder, $list);
		if (isset($list[$this->getName()])) unset($list[$this->getName()]);
		$this->getDataGrid()->defaultOrder = http_build_query($list, '', '&');
		
		return $this;
	}
	
	
	/**
	 * Removes data grid's default filtering.
	 * @return DataGridColumn  provides a fluent interface
	 */
	public function removeDefaultFiltering()
	{
		parse_str($this->getDataGrid()->defaultFilters, $list);
		if (isset($list[$this->getName()])) unset($list[$this->getName()]);
		$this->getDataGrid()->defaultFilters = http_build_query($list, '', '&');
		
		return $this;
	}



	
	/********************* filter factories *********************/



	/**
	 * Alias for method addTextFilter().
	 * @return IDataGridColumnFilter
	 */
	public function addFilter()
	{
		return $this->addTextFilter();
	}


	/**
	 * Adds single-line text filter input to data grid.
	 * @return IDataGridColumnFilter
	 * @throws InvalidArgumentException
	 */
	public function addTextFilter()
	{
		$filter = new TextFilter();
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}


	/**
	 * Adds single-line text date filter input to data grid.
	 * Optional dependency on DatePicker class (@link http://nettephp.com/extras/datepicker)
	 * @return IDataGridColumnFilter
	 * @throws InvalidArgumentException
	 */
	public function addDateFilter()
	{
		$filter = new DateFilter();
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}


	/**
	 * Adds check box filter input to data grid.
	 * @return IDataGridColumnFilter
	 * @throws InvalidArgumentException
	 */
	public function addCheckboxFilter()
	{
		$filter = new CheckboxFilter();
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}


	/**
	 * Adds select box filter input to data grid.
	 * @param  array   items from which to choose
	 * @param  int     skip first items value from validation?
	 * @param  bool    translate all items in selectbox?
	 * @return IDataGridColumnFilter
	 * @throws InvalidArgumentException
	 */
	public function addSelectboxFilter($items = NULL, $skipFirst = NULL, $translateItems = TRUE)
	{
		$filter = new SelectboxFilter($items, $skipFirst);
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}
}