<?php


/**
 * Tridy filter jsou jen tzv gettery Form Controlu filtracniho pole, proto zde neni metoda pro 
 * aplikovani filtru a je v tride pro sloupec. Navic kdyz si nekdo bude chtit tridu 
 * pro sloupec rozsirit, muze to udelat vcetne aplikovani filtru aniz by musel rozsirovat dve tridy. 
 * 
 * @author Roman
 *
 */
class TextFilter extends Component implements IDataGridColumnFilter
{
	/** @var FormControl  form element */
	protected $element;
	
	/** @var string  value of filter (if was filtered) */
	public $value;	

	
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Gets filters form element.
	 * @return FormControl
	 */
	public function getFormControl()
	{
		if ($this->element instanceof FormControl) return $this->element;

		$this->element = new TextInput($this->getName(), 5);
		$dataGrid = $this->lookup('DataGrid', TRUE);
		
		return $this->element;
	}
	
	
	/**
	 * Gets filter's value, if was filtered.
	 * @return string
	 */
	public function getValue()
	{
		$dataGrid = $this->lookup('DataGrid', TRUE);
		
		// set value if was grid filtered yet
		parse_str($dataGrid->filters, $list);
		foreach ($list as $key => $value) {
			if ($key == $this->getName()) {
				$this->value = $value;
				break;
			}
		}		
		return $this->value;
	}

}



class DateFilter extends TextFilter
{	
	/**
	 * Gets filters form element.
	 * @return FormControl
	 */
	public function getFormControl()
	{
		parent::getFormControl();
		$this->element->getControlPrototype()->class('datepicker');		
		return $this->element;
	}
}



class CheckboxFilter extends TextFilter implements IDataGridColumnFilter
{
	/**
	 * Gets filters form element.
	 * @return FormControl
	 */
	public function getFormControl()
	{
		if ($this->element instanceof FormControl) return $this->element;		
		$element = new Checkbox($this->getName());
		
		return $this->element = $element;
	}

}



class SelectboxFilter extends TextFilter implements IDataGridColumnFilter
{	
	/** @var array  asociative array of items in selectbox */
	protected $generatedItems;
	
	/** @var array  asociative array of items in selectbox */
	protected $items;
	
	/** @var bool  skip first item from validation? */
	protected $skipFirst;	

	
	public function __construct(array $items = NULL, $skipFirst = NULL)
	{
		$this->items = $items;
		$this->skipFirst = $skipFirst;
		parent::__construct();
	}


	/**
	 * Gets filters form element.
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
		
		return $this->element;
	}
	
	
	/**
	 * Generates selectbox items.
	 * @return array
	 */
	public function generateItems()
	{
		// NOTE: pokud byly items predany v konstruktoru tak negeneruj
		if (is_array($this->items)) return;
		
		$dataGrid = $this->lookup('DataGrid', TRUE);
		
		$columnName = $this->getName();
		$dataSource = clone $dataGrid->dataSource;
		$dataSource->release();

		$cond = array();
		$cond[] = array("[$columnName] NOT LIKE %s", '');
			
		$dataSource->where('%and', $cond)->orderBy($columnName)->applyLimit(NULL);
		$items = $dataSource->fetchPairs($columnName, $columnName);
		ksort($items);
		
		$this->generatedItems = array_merge(array('?' => '?'), $items);
		$this->skipFirst = TRUE;
		
		// if was grid filtered yet by this filter don't update with filtred items (keep full list)
		if (empty($this->element->value)) {
			$this->element->setItems($this->generatedItems);
		}
		
		return $this->items;
	}

}

