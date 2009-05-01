<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Forms
 * @version    $Id$
 */



require_once LIBS_DIR . '/Nette/Component.php';



/**
 * Base class that implements the basic functionality common to form controls.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Extras\DataGrid
 */
abstract class DataGridColumn extends Component implements IDataGridColumn
{	
	/** @var Html  control table header element template */
	protected $header;
	
	/** @var Html  control table cell element template */
	protected $cell;

	/** @var string textual caption or label */
	public $caption;
	
	/** @var int  maximum number of dislayed characters */
	protected $maxLength = 100;

	/** @var array of arrays('pattern' => 'replacement') */
	public $replacement;
	
	/** @var array  of callback function */
	public $formatCallback = array();

	/** @var array user options */
	private $options = array();
	
	/** @var bool */
	public $orderable = TRUE;



	/**
	 * @param  string  column caption
	 * @param  int  width in pixels of the control (will be setted by css)
	 * @param  int  maximum number of dislayed characters
	 */
	public function __construct($caption = NULL, $maxLength = NULL)
	{
		parent::__construct();
		$this->header = Html::el('');
		$this->cell = Html::el('');
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
				$this->caption = String::capitalize($this->getName());
			}
		}
	}
	
	
	/**
	 * Returns DataGrid.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return Form
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
	
	
	
	/********************* interface IDataGridColumn *********************/
	
	
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
	 * @return string
	 */
	public function getLink()
	{
		return $this->lookup('DataGrid', TRUE)->link('order', $this->getName());
	}
	
	
	public function hasFilter()
	{
		return $this->getDataGrid(TRUE)->getComponent('filters')->getComponent($this->getName(), FALSE) instanceof IDataGridColumnFilter;
	}
	
	
	public function getFilter()
	{
		if ($this->hasFilter()) {
			return $this->getDataGrid(TRUE)->getComponent('filters', TRUE)->getComponent($this->getName());
		} else {
			return NULL;
		}
	}
	
	
	/**
	 * @param string  value to be formated
	 */
	public function formatContent($value)
	{
		trigger_error('DataGridColumn::formatContent should not be called; Overload this method by your implementation in descendant.', E_USER_WARNING);
		return $value;
	}
	
		
	/**
	 * Applies filtering on dataset.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		trigger_error('DataGridColumn::applyFilter should not be called; Overload this method by your implementation in descendant.', E_USER_WARNING);
		return;
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
	 * Adds single-line text input 
	 * @return IDataGridColumnFilter
	 * @throws InvalidArgumentException
	 */
	public function addTextFilter()
	{
		$filter = new TextFilter();
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}
	
	
	public function addDateFilter()
	{
		$filter = new DateFilter();
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}



	/**
	 * Adds check box control to the form.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  string  caption
	 * @return Checkbox
	 */
	public function addCheckboxFilter()
	{
		$filter = new CheckboxFilter();
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}



	/**
	 * Adds select box control that allows single item selection.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  array   items from which to choose
	 * @param  int     number of rows that should be visible
	 * @return SelectBox
	 */
	public function addSelectboxFilter($items = NULL, $skipFirst = NULL)
	{
		$filter = new SelectboxFilter($items, $skipFirst);
		$this->getDataGrid(TRUE)->getComponent('filters', TRUE)->addComponent($filter, $this->getName());
		return $filter;
	}
}



class TextColumn extends DataGridColumn
{
	
	/**
	 * @param string  value to be formated
	 * @param string  primary key value of the first param
	 */
	public function formatContent($value)
	{
		$value = htmlSpecialChars($value);
		if (is_array($this->replacement) && !empty($this->replacement)) {
			if (in_array($value, array_keys($this->replacement))) {
				$value = $this->replacement[$value];
			}
		}
		$value = String::truncate((string) $value, $this->maxLength);		
	
		foreach ($this->formatCallback as $callback) {
			if (is_callable($callback)) {
        		$value = call_user_func($callback, $value);
			}
		}
		return $value;
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
		$cond[] = array("[$column] LIKE '%$value%'");
		$datagrid->dataSource->where('%and', $cond);
	}

}


class NumericColumn extends DataGridColumn
{
	/** @var int  number of digits after the decimal point */
	public $precision;
	
	
	public function __construct($caption = NULL, $precision = 2)
	{
		parent::__construct($caption);
		$this->precision = $precision;
	}
	
	public function formatContent($value)
	{
		if (is_array($this->replacement) && !empty($this->replacement)) {
			if (in_array($value, array_keys($this->replacement))) {
				$value = $this->replacement[$value];
			}
		}		
		$value = round($value, $this->precision);
	
		foreach ($this->formatCallback as $callback) {
			if (is_callable($callback)) {
        		$value = call_user_func($callback, $value);
			}
		}
		return $value;
	}	
	
	/**
	 * Applies filtering on dataset.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) return;
		
		$column = $this->getName();
		$cond = array();
		$operator = '=';
		
		$v = str_replace(',', '.', $value);
		if (preg_match('/^(?<operator>\>|\>\=|\<|\<\=|\=|\<\>)?(?<value>[\.|\d]+)$/', $v, $matches)) {
			if (isset($matches['operator']) && !empty($matches['operator'])) {
				$operator = $matches['operator'];
			}
			$value = $matches['value'];
		}
		$cond[] = array("[$column] $operator %s", $value);

		$datagrid = $this->getDataGrid(TRUE);
		$datagrid->dataSource->where('%and', $cond);
	}
}


class DateColumn extends TextColumn
{
	/** @var string  database date format */
	public $format;
	
	
	public function __construct($caption = NULL, $format = '%x')
	{
		parent::__construct($caption);
		$this->format = $format;
		$this->getHeaderPrototype()->style('width: 80px');
	}
	
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


class CheckboxColumn extends NumericColumn
{	
	public function __construct($caption = NULL)
	{
		parent::__construct($caption, 0);
		$this->getCellPrototype()->style('text-align: center');
	}
	
	public function formatContent($value)
	{		
		$checkbox = Html::el('input')->type('checkbox')->disabled('disabled');
		if ($value) $checkbox->checked = TRUE;
		return (string) $checkbox;
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
		$value = (int)(bool)$value;
		$cond = array();
		$cond[] = array("[$column] " . ($value ? ">" : "=") . " %b", FALSE);
		
		$datagrid->dataSource->where('%and', $cond);
	}
}


class PositionColumn extends NumericColumn
{
	/** @var array */
	public $moves = array();
	
	/** @var string  signal handler of move action */
	public $destination;
	
	/** @var bool */
	public $useAjax;
	
	/** @var string */
	static public $ajaxClass = 'ajaxlink';
	
	public function __construct($caption = NULL, $destination = NULL, array $moves = NULL, $useAjax = FALSE)
	{
		parent::__construct($caption, 0);
		
		$this->useAjax = $useAjax;
		
		if ($moves === NULL) {
			$this->moves['up'] = 'Move up';
			$this->moves['down'] = 'Move down';
		} else {
			$this->moves = $moves;
		}
		
		// try set handler if is not set
		if ($destination === NULL) {
			if ($presenter = $this->lookup('Nette\Aplication\Presenter') !== NULL) {
				$this->destination = 'handle' . String::capitalize($this->getName) . 'Move!';
			}
		} else {
			$this->destination = $destination;
		}
		
	}
	
	public function formatContent($value)
	{
		$dataSource = clone $this->getDataGrid(TRUE)->dataSource;
		$max = (int)$dataSource->select($this->getName())->orderBy($this->getName(), 'DESC')->fetchSingle();

		$presenter = $this->lookup('Nette\Aplication\Presenter', TRUE);
		$uplink = $presenter->link($this->destination, array('key' => $value, 'dir' => 'up'));
		$downlink = $presenter->link($this->destination, array('key' => $value, 'dir' => 'down'));
		
		$up = Html::el('a')->title($this->moves['up'])->href($uplink)->add(Html::el('span')->class('up')->setHtml('&nbsp;'));
		$down = Html::el('a')->title($this->moves['down'])->href($downlink)->add(Html::el('span')->class('down')->setHtml('&nbsp;'));
		
		// disable top up & top bottom links
		if ($value == 1) {
			$up->href(NULL);
			$up->class('inactive');
		}
		if ($value == $max) {
			$down->href(NULL);
			$down->class('inactive');
		}		
		if ($this->useAjax) {
			$up->class(self::$ajaxClass);
			$down->class(self::$ajaxClass);
		}
		
		$positioner = Html::el('span')->class('positioner')->add($up)->add($down);
		return $positioner . '&nbsp;' . $value;
	}
}


class ImageColumn extends TextColumn
{
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		throw new NotImplementedException("Class was not implemented yet.");
	}
}


class ActionColumn extends DataGridColumn
{
	// TODO: move actions from datagrid here?
	/** @var ComponentContainer */
	//public $actions = array();
	
	/** @var int */
	//public $count = 0;
	
	public function __construct($caption = 'Actions')
	{
		parent::__construct($caption);
		$this->orderable = FALSE;
	}
	
	
	public function formatContent($value)
	{
		trigger_error('ActionColumn cannot be formated.', E_USER_WARNING);
		// TODO: or throw exception?
		//throw new InvalidStateException("ActionColumn cannot be formated.");
		return $value;
	}
	
	
	public function applyFilter($value)
	{
		trigger_error('ActionColumn cannot be filtered.', E_USER_WARNING);
		// TODO: or throw exception?
		//throw new InvalidStateException("ActionColumn cannot be filtered.");
		return;
	}	
}