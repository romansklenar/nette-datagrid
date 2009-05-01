<?php


require_once LIBS_DIR . '/Nette/Application/Control.php';

class DataGrid extends Control implements ArrayAccess, INamingContainer
{
	/** @persistent */
	public $page = 1;

	/** @persistent */
	public $order = '';

	/** @persistent */
	public $filters = '';

	/** @var int */
	protected $rowsPerPage = 15;

	/** @var DibiDataSource */
	protected $dataSource;

	/** @var Paginator */
	protected $paginator;

	/** @var bool  multi column order */
	public $multiOrder = TRUE;

	/** @var bool  left side column of checkboxes to allow mass operations */
	protected $rowsChecker = FALSE;

	/** @var array  list of mass operations */
	public $operations = array();

	/** @var array  of valid callback(s) */
	protected $onOperationSubmit;	

	/** @var IDataGridRenderer */
	protected $renderer;

	/** @var string */
	public $tableName = '';
	
	/** @var string */
	protected $keyName;
	
	/** @var bool */
	protected $isPaging;
	
	/** @var bool */
	protected $isSorting;
	
	/** @var bool */
	protected $isFiltering;


	public function __construct()
	{
		parent::__construct();
		$this->paginator = new Paginator;
		$this->paginator->itemsPerPage = $this->rowsPerPage;

		$this->addComponent(new ComponentContainer(), 'columns');
		$this->addComponent(new ComponentContainer(), 'filters');
		$this->addComponent(new ComponentContainer(), 'actions');
	}


	/**
	 * Pokud je dotaz pro získáni dat pro DataGrid složen z více tabulek, druhý parametr udá
	 * Název tabulky slouží k získání meta dat o primárním klíči
	 * @param DibiDataSource
	 * @param string  if is table name setted, allows autodetection of key name
	 * @throws DibiException
	 */
	public function bindDataTable(DibiDataSource $dataSource, $tableName = NULL)
	{
		$this->dataSource = $dataSource;
		$this->paginator->itemCount = count($dataSource);

		if (isset($tableName)) {
			// oveření zda tabulka v databázi existuje (v opačném případě vyhazuje výjimku)
			$tableInfo = $this->dataSource->getConnection()->getDatabaseInfo()->getTable($tableName);
			$this->tableName = $tableInfo->getName();
		}
	}


	/**
	 * Property method
	 * @return DibiDataSource
	 */
	public function getDataSource()
	{
		return $this->dataSource;
	}


	/**
	 * Slouží pro určení klíče, nad kterým se budou provádět hromadné operace.
	 * Např. pomocí checkboxů vyberu více záznamů, které chci smazat, formulář odešlu a v handleru vidím,
	 * nad kterýma hodnotama tohoto primárního klíče mám akci provést.
	 * Pokud více klíčů tvoří primární klíč, první parametr určí jméno klíče, který se má použít.
	 *
	 * @param string
	 * @return DibiIndexInfo
	 */
	public function getKeyName($name = NULL)
	{
		if ($this->keyName != NULL) {
			// if was setted by setter
			return $this->keyName;
			
		} else {
			// try autodetection
			try {
				$tableInfo = $this->dataSource->getConnection()->getDatabaseInfo()->getTable($this->tableName);
				$columnsInfo = $tableInfo->getPrimaryKey()->getColumns();
				if (count($columnsInfo) == 1) {
					$col = $columnsInfo[0];
					return$col->getName();
		
				} else {
					foreach ($columnsInfo as $columnInfo) {
						if ($columnInfo->getName() == $name) {
							return $columnInfo->getName();
						}
					}
		
					throw new InvalidArgumentException("Primary key '$name' not found in table {$this->tableName}.");
				}
			} catch (Exception $e) {
				Debug::processException($e);
				throw new InvalidStateException("Name of key for group operations or actions was not set for DataGrid '" . $this->getName() . "'.");
			}
		}
	}
	
	
	public function setKeyName($key)
	{
		$this->keyName = $key;
	}


	/********************* public getters and setters *********************/


	/**
	 * Public setter / property
	 * Defines number of rows per one page on the grid.
	 * @param int
	 * @return void
	 */
	public function setRowsPerPage($value)
	{
		if ($value <= 0) {
			throw new InvalidArgumentException("");
		}
		$this->paginator->itemsPerPage = $this->rowsPerPage = (int) $value;
	}


	/**
	 * Public getter / property
	 * @return int
	 */
	public function getRowsPerPage()
	{
		return (int) $this->rowsPerPage;
	}


	/**
	 * Public getter / property
	 * @return bool
	 */
	public function getRowsChecker()
	{
		return (bool) $this->rowsChecker;
	}


	/**
	 * Public getter / property
	 * Generates list of pages used for visual control.
	 * @return array
	 */
	public function getSteps()
	{
		// paginator steps
		$arr = range(max($this->paginator->firstPage, $this->page - 3), min($this->paginator->lastPage, $this->page + 3));
		$count = 15;
		$quotient = ($this->paginator->pageCount - 1) / $count;
		for ($i = 0; $i <= $count; $i++) {
			$arr[] = round($quotient * $i) + $this->paginator->firstPage;
		}
		sort($arr);

		return array_values(array_unique($arr));
	}
	
	
	public function setOnOperationSubmit($callback)
	{
		if (!is_array($this->onOperationSubmit)) {
			$this->onOperationSubmit = array();
		}
		$this->onOperationSubmit[] = $callback;
	}
	
	
	public function getOnOperationSubmit()
	{
		return $this->onOperationSubmit;
	}


	/********************* Iterators getters and ComponentContainer handlers *********************/


	/**
	 * Iterates over all datagrid rows.
	 * @return ArrayIterator
	 * @throws InvalidStateException
	 */
	public function getRows()
	{
		if (!$this->dataSource instanceof DibiDataSource) {
			throw new InvalidStateException("Data source has not been set or has invalid data type. You must set data source before you want get rows.");
		}
		return $this->dataSource->getIterator();
	}


	/**
	 * Iterates over all datagrid columns.
	 * @return ArrayIterator
	 * @throws InvalidArgumentException
	 */
	public function getColumns()
	{
		return $this->getComponent('columns', TRUE)->getComponents(FALSE, 'IDataGridColumn');
	}	


	/**
	 * Iterates over all datagrid filters.
	 * @return ArrayIterator
	 * @throws InvalidArgumentException
	 */
	public function getFilters()
	{
		return $this->getComponent('filters', TRUE)->getComponents(FALSE, 'IDataGridColumnFilter');
	}


	/**
	 * Iterates over all datagrid actions.
	 * @return ArrayIterator
	 * @throws InvalidArgumentException
	 */
	public function getActions()
	{
		return $this->getComponent('actions', TRUE)->getComponents(FALSE, 'IDataGridAction');
	}

	
	/**
	 * Does any of datagrid columns has filter?
	 * @return bool
	 */
	public function hasColumns()
	{
		foreach ($this->getColumns() as $column) {
			return TRUE;
		}
		return FALSE;
		
		// return count($this->getColumns()->getInnerIterator()) > 0;
	}
	

	/**
	 * Does any of datagrid columns has filter?
	 * @return bool
	 */
	public function hasFilters()
	{
		foreach ($this->getFilters() as $filter) {
			return TRUE;
		}
		return FALSE;
		
		// return count($this->getFilters()->getInnerIterator()) > 0;
	}


	/**
	 * Does datagrid has any action?
	 * @return bool
	 */
	public function hasActions()
	{
		foreach ($this->getActions() as $action) {
			return TRUE;
		}
		return FALSE;
		
		// return count($this->getActions()->getInnerIterator()) > 0;
	}
	
	
	/**
	 * Does datagrid has any group operation?
	 * @return bool
	 */
	public function hasOperations()
	{
		return count($this->operations) >= 1;
	}
	
	
	/**
	 * Does datagrid has a checker?
	 * @return bool
	 */
	public function hasChecker()
	{
		return $this->rowsChecker;
	}



	/********************* signal handlers ********************/



	/**
	 * Changes page number.
	 */
	public function handlePage($page)
	{
		$this->isPaging = TRUE;
		$this->paginator->page = $page;
		$this->invalidateControl('grid');
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}


	/**
	 * Changes column sorting order.
	 */
	public function handleOrder($by)
	{
		$this->isPaging = TRUE;
		parse_str($this->order, $list);

		if (!isset($list[$by])) {
			if (!$this->multiOrder) {
				$list = array();
			}
			$list[$by] = 'a';

		} elseif ($list[$by] === 'd') {
			if ($this->multiOrder) {
				unset($list[$by]);
			} else {
				$list[$by] = 'a';
			}

		} else {
			$list[$by] = 'd';

		}

		$this->order = http_build_query($list, '', '&');
		$this->invalidateControl('grid');

		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
	

	public function handleFilter($by)
	{
		$this->isFiltering = TRUE;
		$filters = array();
		foreach ($by as $key => $value) {
			if ($value !== '') $filters[$key] = $value;
		}
		$this->filters = http_build_query($filters, '', '&');
		$this->invalidateControl('grid');
		$this->invalidateControl('paginator');
		
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
	
	
	
	/********************* submit handlers *********************/
	
	
	
	/**
	 * Table-select form submit handler
	 * @param $form AppForm
	 * @return void
	 */
	public function onSubmitHandler(AppForm $form)
	{
		// byl odeslán?
		if ($form->isSubmitted() && $form->isValid()) {
			$values = $form->getValues();

			if ($form['filterSubmit']->isSubmittedBy()) {
				$this->handleFilter($values['filters']);
					
			} elseif ($form['topSubmit']->isSubmittedBy() || $form['bottomSubmit']->isSubmittedBy()) {
				// NOTE: tyto odesilaci tlacitka na hromadne operace ignoruj,
				// protoze by mely mit uzivatelem definovan svuj handler v presenteru nebo komponente
				trigger_error('No user defined handler for group operations; assign valid callback to your group operations handler into DataGrid::$operationsHandler variable.', E_USER_WARNING);
				return;

			} else {
				// unknown submit button
				throw new InvalidStateException("Unknown submit button.");
			}

		} else {
			// první zobrazení, nastavíme výchozí hodnoty
			//$form->setDefaults($defaults);
		}

		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}


	/**
	 * Table-select form submit handler
	 * @param $button Button
	 * @return void
	 */
	public function onClickFilterHandler(Button $button)
	{
		//$this->presenter->flashMessage("onClickFilterSubmit: odesláno přes tlačítko 'filterSubmit'.", 'info');
		$this->onSubmitHandler($button->getParent());
	}
	
	
	
	/********************* applycators (call before rendering only) *********************/
	


	protected function applyPaging()
	{
		// paging
		if ($this->isFiltering && !$this->isPaging) {
			$this->paginator->page = $this->page = 1;			
		} else {
			$this->paginator->page = $this->page;
		}
		
		$this->paginator->itemCount = count($this->dataSource);
		$this->dataSource->applyLimit($this->paginator->length, $this->paginator->offset);
	}


	protected function applySorting()
	{
		// sorting
		$i = 1;
		parse_str($this->order, $list);
		foreach ($list as $field => $dir) {
			$this->dataSource->orderBy($field, $dir === 'a' ? dibi::ASC : dibi::DESC);
			$list[$field] = array($dir, $i++);
		}
		//$sql = $this->dataSource->__toString();
		return $list;
	}


	protected function applyFiltering()
	{
		if (!$this->hasFilters()) return;
		
		// filtering
		parse_str($this->filters, $list);
		foreach ($list as $column => $value) {
			if ($value !== '') {
				$this->getComponent('columns', TRUE)->getComponent($column, TRUE)->applyFilter($value);
			}
		}
	}


	
	/********************* renderers *********************/
	

	
	/**
	 * Sets datagrid renderer.
	 * @param  IFormRenderer
	 * @return void
	 */
	public function setRenderer(IDataGridRenderer $renderer)
	{
		$this->renderer = $renderer;
	}


	/**
	 * Returns datagrid renderer.
	 * @return IDataGridRenderer|NULL
	 */
	public function getRenderer()
	{
		if ($this->renderer === NULL) {
			$this->renderer = new DataGridRenderer;
		}
		return $this->renderer;
	}
	
	
	/**
	 * Returns datagrid's form component.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return Form
	 */
	public function getForm($need = TRUE)
	{
		return $this->_getComponent('form', $need);
	}
	
	
	/**
	 * Renders table grid.
	 * @return void
	 */
	public function renderGrid()
	{
		$args = func_get_args();
		array_unshift($args, $this);
		$s = call_user_func_array(array($this->getRenderer(), 'render'), $args);

		echo mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
	}
		
	/**
	 * Renders table grid.
	 * @return void
	 */
	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(dirname(__FILE__) . '/grid.phtml');
		$template->form = $this->_getComponent('form', TRUE, TRUE);
		$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
		$template->render();
	}
	
	
	/**
	 * Renders paginator.
	 */
	public function renderPaginator()
	{
		if ($this->paginator->pageCount < 2) return;
		$this->paginator->page = $this->page;

		// render
		$template = $this->createTemplate();
		$template->paginator = $this->paginator;
		$template->setFile(dirname(__FILE__) . '/paginator.phtml');
		$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
		$template->steps = $this->getSteps();
		$template->render();
	}
	
	

	/********************* components handling *********************/
	
	
	
	/**
	 * Component factory
	 * @see Nette/ComponentContainer#createComponent()
	 */
	protected function createComponent($name)
	{
		switch ($name) {
			case 'form':
				$form = new AppForm($this, $name);
				$form->getElementPrototype()->class = 'gridform';
				FormControl::$idMask = 'frm-grid' . String::capitalize($this->getName()) . '-%s-%s';
				//$form->setRenderer(new CustomRenderer);

				// NOTE: aby fungovalo filtrování po odeslání klávesou ENTER,
				// musí být toto tlačítko vykresleno jako první (i pokud je pouzito manualni vykresleni)
				$form->addSubmit('filterSubmit', 'Aplikuj filtr')
					->onClick[] = array($this, 'onClickFilterHandler');

				$form->addSelect('topAction', 'Označené:', $this->operations);
				$form->addSubmit('topSubmit', 'Provést')
					->onClick= $this->onOperationSubmit;
					
				$form->addSelect('bottomAction', 'Označené:', $this->operations);
				$form->addSubmit('bottomSubmit', 'Provést')
					->onClick= $this->onOperationSubmit;

				//$form->onSubmit[] = array($this, 'onSubmitHandler');
				
				// vygeneruj filtracni pole, pokud je treba
				if ($this->hasFilters()) {
					$sub = $form->addContainer('filters');
					foreach ($this->getFilters() as $filter) {
						$sub->addComponent($filter->getFormControl(), $filter->getName());
						// NOTE: must be setted after is FormControl conntected to the form
						$sub->getComponent($filter->getName(), TRUE)->setValue($filter->getValue());
					}
				}
				
				if ($this->rowsChecker) {
					$primary = $this->getKeyName();

					$sub = $form->addContainer('checker');
					foreach ($this->getRows() as $row) {
						$sub->addCheckbox($row[$primary], $row[$primary]);
					}
				}

				$renderer = $form->getRenderer();
				$renderer->wrappers['controls']['container'] = NULL;
				$renderer->wrappers['label']['container'] = NULL;
				$renderer->wrappers['control']['container'] = NULL;
				$form->setRenderer($renderer);
				return;

			default:
				parent::createComponent($name);
				return;
		}
	}
	
	
	/**
	 * Returns component specified by name or path.
	 * @param  string
	 * @param  bool   throw exception if component doesn't exist?
	 * @param  bool   generate form controls for component 'form'? 
	 * @return IComponent|NULL
	 * 
	 * @note: Duvod vzniku teto metody: pokud formular prijima signal na zpracovani filtru, 
	 * tak by se komponenta 'fomr' vytvorila pred renderovanim a uz by na ni nesly spravne 
	 * aplikovat tyto filtry, protoze cely container checker by byl naplnen komponentama/checkboxy,
	 * ktere by se vazaly k neaktualnim radkum.
	 * Dokud je metoda ComponentContainer::getComponent() final, musim takto Nette obchazet podtrzitkovou verzi teto metody.
	 * 
	 * Mozne (ne moc ciste) reseni, ktere by eliminovalo nutnost teto metody, 
	 * je generovat do checkeru rovnou vsechny checkboxy a neomezovat se jen na ty mezi ofsett a limit, 
	 * ale pri pak by nalezela na kazdy radek v tabulce jedna komponta (checkbox) => aplikace by se zpomalovala.
	 * 
	 * Dalsi reseni by bylo rozdelit filtracni cast a checkboxovou cast formulare do dvou nezavislych formularu,
	 * to ale nepripada v uvahu kvuli nevalidniho html kodu a par omezeni, ktere by to prineslo.
	 */
	public function _getComponent($name, $need = TRUE, $regenerate = NULL)
	{
		$component = parent::getComponent($name, $need);
		
		// TODO: regenerate if is datagrid signal receiver only
		if ($name == 'form' && $regenerate == TRUE) {
			$this->regenerateFormControls($component);
		}		
		return $component;
	}
	

	/**
	 * Generates filters and checker form controls
	 * @param  AppForm $form
	 * @return void
	 */
	protected function regenerateFormControls(AppForm $form)
	{
		// vyfiltruj zaznamy, aby se controly vytvarely jen na pouzitych zaznamech, musi byt v tomto poradi
		$this->applyFiltering();
		$this->applySorting();
		$this->applyPaging();
			
		// vygeneruj checkboxy pro hromadne operace, pokud je treba
		if ($this->rowsChecker) {
			$primary = $this->getKeyName();
			$form->removeComponent($form['checker']);
			$sub = $form->addContainer('checker');
			foreach ($this->getRows() as $row) {
				$sub->addCheckbox($row[$primary], $row[$primary]);
			}
		}
		
		// pro filtracni selectboxy aktualizuj hodnoty pokud doslo k filtrovani
		if ($this->hasFilters() && $this->isFiltering) {
			foreach ($this->getFilters() as $filter) {
				if ($filter instanceof SelectboxFilter) {
					$filter->generateItems();
				}
			}
		}
		
		return;
	}
	
	
	
	
	public function allowOperations(array $operations, $callback = NULL, $key = NULL)
	{
		$this->operations = $operations;
		$this->rowsChecker = TRUE;
		
		if ($key != NULL && $this->keyName == NULL) {
			$this->setKeyName($key);
		}
		if ($callback != NULL && $this->onOperationSubmit == NULL) {
			 $this->setOnOperationSubmit($callback);
		}
	}

	
	/********************* control factories *********************/
	

	/**
	 * Adds single-line text presented column of values.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  int  width in pixels of the control (will be setted by css)
	 * @param  int  maximum number of dislayed characters
	 * @return IDataGridColumn
	 */
	public function addColumn($name, $caption = NULL, $maxLength = NULL)
	{
		return $this[$name] = new TextColumn($caption, $maxLength);
	}
	
	
	/**
	 * Adds single-line numeric presented column of values.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  int  number of digits after the decimal point
	 * @return IDataGridColumn
	 */
	public function addNumericColumn($name, $caption = NULL, $precision = 2)
	{
		return $this[$name] = new NumericColumn($caption, $precision);
	}
	
	
	/**
	 * Adds single-line text input control to the form.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  int  database date format
	 * @return IDataGridColumn
	 */
	public function addDateColumn($name, $caption = NULL, $format = '%x')
	{
		return $this[$name] = new DateColumn($caption, $format);
	}
	
	
	/**
	 * Adds single-line text input control to the form.
	 * @param  string  control name
	 * @param  string  column label
	 * @return IDataGridColumn
	 */
	public function addCheckboxColumn($name, $caption = NULL)
	{
		return $this[$name] = new CheckboxColumn($caption);
	}


	/**
	 * Adds graphical button used to submit form.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  string  URI of the image
	 * @param  string  alternate text for the image
	 * @return ImageButton
	 */
	public function addImageColumn($name, $caption = NULL/*, $src, $alt = NULL*/)
	{
		return $this[$name] = new ImageColumn($caption/*, $src, $alt*/);
	}
	
	
	/**
	 * Adds ...
	 * @param  string  control name
	 * @param  string  column label
	 * @return ImageButton
	 */
	public function addPositionColumn($name, $caption = NULL, $destination = NULL, array $moves = NULL)
	{
		return $this[$name] = new PositionColumn($caption, $destination, $moves);
	}
	
	
	/**
	 * Adds ...
	 * @param  string  column label
	 * @return ActionColumn
	 */
	public function addActionColumn($caption)
	{
		return $this['actions'] = new ActionColumn($caption);
	}


	/**
	 * Action factory.
	 */
	public function addAction($title, $signal, $icon = NULL, $useAjax = FALSE, $type = DataGridAction::WITH_KEY)
	{
		if (!$this->getComponent('columns', TRUE)->getComponent('actions', FALSE)) {
			trigger_error('Use DataGrid::addActionColumn before you add actions.', E_USER_WARNING);
		}
		$count = $this->hasActions() ? count($this->getActions()->getInnerIterator()) : 0;
		$action = new DataGridAction($title, $signal, $icon, $useAjax, $type);
		$this->getComponent('actions', TRUE)->addComponent($action, (string)$count);
		return $action;
	}



	/********************* interface \ArrayAccess *********************/



	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  IComponent
	 * @return void.
	 */
	final public function offsetSet($name, $component)
	{
		$this->getComponent('columns', TRUE)->addComponent($component, $name);
	}



	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return IComponent
	 * @throws InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent('columns', TRUE)->getComponent($name, TRUE);
	}



	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent('columns', TRUE)->getComponent($name, FALSE) !== NULL;
	}



	/**
	 * Removes component from the container. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		$component = $this->getComponent('columns', TRUE)->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->getComponent('columns', TRUE)->removeComponent($component);
		}
	}
	
	
	/**
	 * Renders table grid and return as string.
	 * @return string
	 */
	public function __toString()
	{
		$args = func_get_args();
		array_unshift($args, $this);
		$s = call_user_func_array(array($this->getRenderer(), 'render'), $args);
		return mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
	}
}





/**
 * Defines method that must be implemented to allow a component to act like a column control.
 *
 * @author     Roman Sklenar
 * @copyright  Copyright (c) 2009 Roman Sklenar
 * @package    Nette\Extras
 */
interface IDataGridColumn
{
	/**
	 * Is column orderable?
	 * @return bool
	 */
	function isOrderable();
	
	
	/**
	 * Gets header link (order signal)
	 * @return string
	 */
	function getLink();
	
	
	/**
	 * Has column filter box?
	 * @return bool
	 */
	function hasFilter();
	
	
	/**
	 * Returns column's filter.
	 * @return IDataGridColumnFilter|NULL
	 */
	function getFilter();
	
	
	/**
	 * Formats cells content.
	 * @param mixed
	 * @return mixed
	 */
	function formatContent($value);
	
	
	/**
	 * Applies filtering on dataset.
	 * @param  mixed
	 * @return void
	 */
	function applyFilter($value);

}


/**
 * Defines method that must be implemented to allow a component to act like a column control.
 *
 * @author     Roman Sklenar
 * @copyright  Copyright (c) 2009 Roman Sklenar
 * @package    Nette\Extras
 */
interface IDataGridColumnFilter
{

	/**
	 * Returns filter's form element.
	 * @return FormControl
	 */
	function getFormControl();

}


interface IDataGridAction
{
	function getHtml();
}


interface IDataGridRenderer
{
	/**
	 * Provides complete data grid rendering.
	 * @param  DataGrid
	 * @return string
	 */
	function render(DataGrid $dataGrid);
	
}