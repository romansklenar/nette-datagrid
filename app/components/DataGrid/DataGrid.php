<?php

/**
 * This source file is subject to the "New BSD License".
 *
 * For more information please see http://nettephp.com
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @link       http://addons.nette.org/datagrid
 */



/**
 * A data bound list control that displays the items from data source in a table.
 * The DataGrid control allows you to select, sort, and manage these items.
 *
 * <code>
 * $grid = new DataGrid;
 * $grid->bindDataTable($model->findAll($model->table)->toDataSource());
 *
 * $grid->addColumn('column', 'Column caption')->addFilter();
 * $grid['column']->getCellPrototype()->style('text-align: center');
 *
 * $grid->addActionColumn('Actions');
 * $grid->addAction('Edit', 'Item:edit');
 *
 * $presenter->addComponent($grid, 'componentName');
 * </code>
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class DataGrid extends Control implements ArrayAccess, INamingContainer
{
	/** @persistent int */
	public $page = 1;

	/** @persistent string */
	public $order = '';

	/** @persistent string */
	public $filters = '';

	/** @persistent int */
	public $itemsPerPage = 15;

	/** @var array */
	public $displayedItems = array('all', 5, 10, 15, 20, 50, 100);

	/** @var bool  multi column order */
	public $multiOrder = TRUE;

	/** @var bool  disables ordering for all columns */
	public $disableOrder = FALSE;

	/** @var string */
	public $defaultOrder;

	/** @var string */
	public $defaultFilters;

	/** @var array */
	public $operations = array();

	/** @var array  of valid callback(s) */
	protected $onOperationSubmit;

	/** @var bool  can datagrid save his state into session? */
	public $rememberState = FALSE;

	/** @var int|string  session timeout (default: until is browser closed) */
	public $timeout = 0;

	/** @var IDataGridRenderer */
	protected $renderer;

	/** @var DibiDataSource */
	protected $dataSource;

	/** @var Paginator */
	protected $paginator;

	/** @var string */
	public $keyName;

	/** @var string */
	protected $receivedSignal;

	/** @var ActionColumn */
	protected $currentActionColumn;

	/** @var bool  was method render() called? */
	protected $wasRendered = FALSE;

	/** @var ITranslator */
	protected $translator;


	/**
	 * Data grid constructor.
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct(); // intentionally without any arguments (because of session loadState)
		$this->paginator = new Paginator;

		$session = $this->getSession();
		if (!$session->isStarted()) {
			$session->start();
		}
	}


	/**
	 * Binds data source to data grid.
	 * @param  DibiDataSource
	 * @throws DibiException
	 * @return void
	 */
	public function bindDataTable(DibiDataSource $dataSource)
	{
		$this->dataSource = $dataSource;
		$this->paginator->itemCount = count($dataSource);
	}


	/**
	 * Getter / property method.
	 * @return DibiDataSource
	 */
	public function getDataSource()
	{
		return $this->dataSource;
	}



	/********************* public getters and setters *********************/



	/**
	 * Getter / property method.
	 * Generates list of pages used for visual control. Use for your custom paginator rendering.
	 * @return array
	 */
	public function getSteps($count = 15)
	{
		// paginator steps
		$arr = range(max($this->paginator->firstPage, $this->page - 3), min($this->paginator->lastPage, $this->page + 3));
		$quotient = ($this->paginator->pageCount - 1) / $count;
		for ($i = 0; $i <= $count; $i++) {
			$arr[] = round($quotient * $i) + $this->paginator->firstPage;
		}
		sort($arr);

		return array_values(array_unique($arr));
	}


	/**
	 * Getter / property method.
	 * @return Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}


	/**
	 * Setter / property method.
	 * @param  mixed  callback(s) to handler(s) which is called after data grid form operation is submited.
	 * @return void
	 */
	public function setOnOperationSubmit($callback)
	{
		if (!is_array($this->onOperationSubmit)) {
			$this->onOperationSubmit = array();
		}
		$this->onOperationSubmit[] = $callback;
	}


	/**
	 * Getter / property method.
	 * @return array
	 */
	public function getOnOperationSubmit()
	{
		return $this->onOperationSubmit;
	}



	/********************* Iterators getters *********************/



	/**
	 * Iterates over datagrid rows.
	 * @throws InvalidStateException
	 * @return DibiResultIterator
	 */
	public function getRows()
	{
		if (!$this->dataSource instanceof DibiDataSource) {
			throw new InvalidStateException("Data source has not been set or has invalid data type. You must set data source before you want get rows.");
		}
		return $this->dataSource->getIterator();
	}


	/**
	 * Iterates over datagrid columns.
	 * @param  string
	 * @throws InvalidArgumentException
	 * @return ArrayIterator
	 */
	public function getColumns($type = 'IDataGridColumn')
	{
		$columns = new ArrayObject();
		foreach ($this->getComponents(FALSE, $type) as $column) {
			$columns->append($column);
		}
		return $columns->getIterator();
	}


	/**
	 * Iterates over datagrid filters.
	 * @param  string
	 * @throws InvalidArgumentException
	 * @return ArrayIterator
	 */
	public function getFilters($type = 'IDataGridColumnFilter')
	{
		$filters = new ArrayObject();
		foreach ($this->getColumns() as $column) {
			if ($column->hasFilter()) {
				$filter = $column->getFilter();
				if ($filter instanceof $type) {
					$filters->append($column->getFilter());
				}
			}
		}
		return $filters->getIterator();
	}


	/**
	 * TODO: throw new DeprecatedException
	 * Iterates over all datagrid actions.
	 * @param  string
	 * @throws InvalidArgumentException
	 * @return ArrayIterator
	 */
	public function getActions($type = 'IDataGridAction')
	{
		$actions = new ArrayObject();
		foreach ($this->getColumns('ActionColumn') as $column) {
			if ($column->hasAction()) {
				foreach ($column->getActions() as $action) {
					if ($action instanceof $type) {
						$actions->append($action);
					}
				}
			}
		}
		return $actions->getIterator();
	}



	/********************* general data grid behavior *********************/



	/**
	 * Does data grid has any row?
	 * @return bool
	 */
	public function hasRows()
	{
		return count($this->getRows()) > 0;
	}


	/**
	 * Does data grid has any column?
	 * @param  string
	 * @return bool
	 */
	public function hasColumns($type = NULL)
	{
		return count($type == NULL ? $this->getColumns() : $this->getColumns($type)) > 0;
	}


	/**
	 * Does any of datagrid columns has a filter?
	 * @param  string
	 * @return bool
	 */
	public function hasFilters($type = NULL)
	{
		return count($type == NULL ? $this->getFilters() : $this->getFilters($type)) > 0;
	}


	/**
	 * Does datagrid has any action?
	 * @param  string
	 * @return bool
	 */
	public function hasActions($type = NULL)
	{
		return count($type == NULL ? $this->getActions() : $this->getActions($type)) > 0;
	}


	/**
	 * Does datagrid has any operation?
	 * @return bool
	 */
	public function hasOperations()
	{
		return count($this->operations) > 0;
	}



	/********************* component's state *********************/



	/**
	 * Loads params
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		if ($this->rememberState) {
			$session = $this->getStateSession();

			if (!isset($session->currentState)) {
				$session->currentState = $session->initState;
			}

			if (isset($session->currentState)) {
				$cs = $session->currentState;
				$is = $session->initState;

				foreach ($cs as $key => $value) {
					if ($cs[$key] != $is[$key]) {

						// additional input validation
						switch ($key) {
							case 'page': $value = ($value > 0 ? $value : 1); break;
							case 'order': break;
							case 'filters': break;
							case 'itemsPerPage': break;
						}
						$params[$key] = $value;
					}
				}
			}
		}
		parent::loadState($params);
	}


	/**
	 * Save params
	 * @param  array
	 * @return void
	 */
	public function saveState(array & $params)
	{
		parent::saveState($params);

		if ($this->rememberState) {
			$session = $this->getStateSession();

			// backup component's state
			if (!isset($session->initState)) {
				$session->initState = array(
					'page' => $this->page,
					'order' => $this->order,
					'filters' => $this->filters,
					'itemsPerPage' => $this->itemsPerPage,
				);
			}

			// save component's state into session
			$session->currentState = $params;
			$session->setExpiration($this->timeout);
		}
	}


	/**
	 * Restores component's state.
	 * @param  string
	 * @return void
	 */
	public function restoreState()
	{
		$session = $this->getStateSession();

		// restore components's init state
		if (isset($session->initState)) {
			$is = $session->initState;
			$this->page = $is['page'];
			$this->order = $is['order'];
			$this->filters = $is['filters'];
			$this->itemsPerPage = $is['itemsPerPage'];
		}

		$session->remove();
	}


	/**
	 * Returns array of classes persistent parameters.
	 * @param  string  class name
	 * @return array
	 */
	public static function getPersistentParams()
	{
		return array('page', 'order', 'filters', 'itemsPerPage');
	}



	/********************* signal handlers ********************/



	/**
	 * Changes page number.
	 * @param  int
	 * @return void
	 */
	public function handlePage($goto)
	{
		$this->page = ($goto > 0 ? $goto : 1);
		$this->invalidateControl();
		if (!$this->presenter->isAjax()) $this->redirect('this');
	}


	/**
	 * Changes column sorting order.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function handleOrder($by, $dir)
	{
		// default ordering
		if (empty($this->order) && !empty($this->defaultOrder)) {
			parse_str($this->defaultOrder, $list);
			if (isset($list[$by])) $this->order = $this->defaultOrder;
			unset($list);
		}

		parse_str($this->order, $list);

		if ($dir == NULL) {
			if (!isset($list[$by])) {
				if (!$this->multiOrder) $list = array();
				$list[$by] = 'a';

			} elseif ($list[$by] === 'd') {
				if ($this->multiOrder) unset($list[$by]);
				else $list[$by] = 'a';

			} else {
				$list[$by] = 'd';
			}

		} else {
			if (!$this->multiOrder) $list = array();
			$list[$by] = $dir;
		}

		$this->order = http_build_query($list, '', '&');
		$this->invalidateControl();
		if (!$this->presenter->isAjax()) $this->redirect('this');
	}


	/**
	 * Prepare filtering.
	 * @param  string
	 * @return void
	 */
	public function handleFilter($by)
	{
		$filters = array();
		foreach ($by as $key => $value) {
			if ($value !== '') $filters[$key] = $value;
		}
		$this->filters = http_build_query($filters, '', '&');

		$this->invalidateControl();
		if (!$this->presenter->isAjax()) $this->redirect('this');
	}


	/**
	 * Change number of displayed items.
	 * @param  string
	 * @return void
	 */
	public function handleItems($value)
	{
		if ($value < 0) {
			throw new InvalidArgumentException("Parametr must be non-negative number, '$value' given.");
		}
		$this->itemsPerPage = $value;

		$this->invalidateControl();
		if (!$this->presenter->isAjax()) $this->redirect('this');
	}


	/**
	 * Change component's state.
	 * @param  string
	 * @return void
	 */
	public function handleReset()
	{
		$this->restoreState();

		$this->invalidateControl();
		if (!$this->presenter->isAjax()) $this->redirect('this');
	}



	/********************* submit handlers *********************/



	/**
	 * Data grid form submit handler.
	 * @param  AppForm
	 * @return void
	 */
	public function formSubmitHandler(AppForm $form)
	{
		$this->receivedSignal = 'submit';

		// was form submitted?
		if ($form->isSubmitted()) {
			$values = $form->getValues();

			if ($form['filterSubmit']->isSubmittedBy()) {
				$this->handleFilter($values['filters']);

			} elseif ($form['pageSubmit']->isSubmittedBy()) {
				$this->handlePage($values['page']);

			} elseif ($form['itemsSubmit']->isSubmittedBy()) {
				$this->handleItems($values['items']);

			} elseif ($form['resetSubmit']->isSubmittedBy()) {
				$this->handleReset();

			} elseif ($form['operationSubmit']->isSubmittedBy()) {
				if (!is_array($this->onOperationSubmit)) {
					throw new InvalidStateException('No user defined handler for operations; assign valid callback to operations handler into DataGrid::$operationsHandler variable.');
				}

			} else {
				throw new InvalidStateException("Unknown submit button.");
			}

		}
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}



	/********************* applycators (call before rendering only) *********************/


	/**
	 * Aplycators caller - filters data grid items.
	 * @return void
	 */
	protected function filterItems()
	{
		// must be in this order
		$this->applyDefaultFiltering();
		$this->applyDefaultSorting();
		$this->applyItems();
		$this->applyFiltering();
		$this->applySorting();
		$this->applyPaging();
	}


	/**
	 * Applies default sorting on data grid.
	 * @return void
	 */
	protected function applyDefaultSorting()
	{
		if (empty($this->order) && !empty($this->defaultOrder)) {
			$this->order = $this->defaultOrder;
		}
	}


	/**
	 * Applies default filtering on data grid.
	 * @return void
	 */
	protected function applyDefaultFiltering()
	{
		if (empty($this->filters) && !empty($this->defaultFilters)) {
			$this->filters = $this->defaultFilters;
		}
	}


	/**
	 * Applies paging on data grid.
	 * @return void
	 */
	protected function applyPaging()
	{
		$this->paginator->page = $this->page;
		$this->paginator->itemCount = count($this->dataSource);

		if ($this->wasRendered && $this->paginator->itemCount < 1 && !empty($this->filters)) {
			// NOTE: don't use flash messages (because you can't - header already sent)
			$this->getTemplate()->flashes[] = (object) array(
				'message' => $this->translate("Used filters did not match any items."),
				'type' => 'info',
			);
		}
		$this->dataSource->applyLimit($this->paginator->length, $this->paginator->offset);
	}


	/**
	 * Applies sorting on data grid.
	 * @return void
	 */
	protected function applySorting()
	{
		$i = 1;
		parse_str($this->order, $list);
		foreach ($list as $field => $dir) {
			$this->dataSource->orderBy($field, $dir === 'a' ? dibi::ASC : dibi::DESC);
			$list[$field] = array($dir, $i++);
		}
		return $list;
	}


	/**
	 * Applies filtering on data grid.
	 * @return void
	 */
	protected function applyFiltering()
	{
		if (!$this->hasFilters()) return;

		parse_str($this->filters, $list);
		foreach ($list as $column => $value) {
			if ($value !== '') {
				$this[$column]->applyFilter($value);
			}
		}
	}


	/**
	 * Applies filtering on data grid.
	 * @return void
	 */
	protected function applyItems()
	{
		$value = (int) $this->itemsPerPage;

		if ($value == 0) {
			$this->itemsPerPage = $this->paginator->itemsPerPage = count($this->dataSource);
		} else {
			$this->itemsPerPage = $this->paginator->itemsPerPage = $value;
		}
	}



	/********************* renderers *********************/



	/**
	 * Sets data grid renderer.
	 * @param  IDataGridRenderer
	 * @return void
	 */
	public function setRenderer(IDataGridRenderer $renderer)
	{
		$this->renderer = $renderer;
	}


	/**
	 * Returns data grid renderer.
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
	 * Renders data grid.
	 * @return void
	 */
	public function render()
	{
		if (!$this->wasRendered) {
			$this->wasRendered = TRUE;

			if (!$this->hasColumns() || (count($this->getColumns('ActionColumn')) == count($this->getColumns()))) {
				$this->generateColumns();
			}

			if ($this->disableOrder) {
				foreach ($this->getColumns() as $column) {
					$column->orderable = FALSE;
				}
			}

			if ($this->hasActions() || $this->hasOperations()) {
				if ($this->keyName == NULL) {
					throw new InvalidStateException("Name of key for operations or actions was not set for DataGrid '" . $this->getName() . "'.");
				}
			}

			if (!count($this->dataSource)) {
				$this->flashMessage($this->translate("Empty datasource given."), 'info');
			}

			// NOTE: important!
			$this->filterItems();

			// TODO: na r20 funguje i: $this->getForm()->isSubmitted()
			if ($this->isSignalReceiver('submit')) {
				$this->regenerateFormControls();
			}
		}

		$args = func_get_args();
		array_unshift($args, $this);
		$s = call_user_func_array(array($this->getRenderer(), 'render'), $args);

		echo mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
	}


	/**
	 * Template factory.
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$template = parent::createTemplate();
		if ($this->getTranslator() !== NULL) {
			$template->setTranslator($this->getTranslator());
		}
		return $template;
	}



	/********************* components handling *********************/



	/**
	 * Component factory.
	 * @see Nette/ComponentContainer#createComponent()
	 */
	protected function createComponentForm($name)
	{
		// NOTE: signal-submit on form disregard component's state
		//		because form is created directly by Presenter in signal handling phase
		//		and this principle is used to detect submit signal
		if (!$this->wasRendered) {
			$this->receivedSignal = 'submit';
		}

		$form = new AppForm($this, $name);
		$form->setTranslator($this->getTranslator());
		FormControl::$idMask = 'frm-datagrid-' . $this->getUniqueId() . '-%s-%s';
		$form->onSubmit[] = array($this, 'formSubmitHandler');

		$form->addSubmit('resetSubmit', 'Reset state');
		$form->addSubmit('filterSubmit', 'Apply filters');

		$form->addSelect('operations', 'Selected:', $this->operations);
		$form->addSubmit('operationSubmit', 'Send')->onClick = $this->onOperationSubmit;

		// page input
		$form->addText('page', 'Page', 1);
		$form['page']->setDefaultValue($this->page);
		$form->addSubmit('pageSubmit', 'Change page');

		// items per page selector
		$form->addSelect('items', 'Items per page', array_combine($this->displayedItems, $this->displayedItems));
		$form['items']->setDefaultValue($this->itemsPerPage);
		$form->addSubmit('itemsSubmit', 'Change');

		// generate filters FormControls
		if ($this->hasFilters()) {
			$defaults = array();
			$sub = $form->addContainer('filters');
			foreach ($this->getFilters() as $filter) {
				$sub->addComponent($filter->getFormControl(), $filter->getName());
				// NOTE: must be setted after is FormControl conntected to the form
				$defaults[$filter->getName()] = $filter->value;
			}
			$sub->setDefaults($defaults);
		}

		// checker
		if ($this->hasOperations()) {
			$sub = $form->addContainer('checker');

			if ($this->isSignalReceiver('submit')) {
				// NOTE: important!
				$ds = clone $this->dataSource;
				$this->filterItems();
			}

			foreach ($this->getRows() as $row) {
				$sub->addCheckbox($row[$this->keyName], $row[$this->keyName]);
			}

			if (isset($ds)) $this->dataSource = $ds;
		}

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		$form->setRenderer($renderer);
		return;
	}


	/**
	 * Returns data grid's form component.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return AppForm
	 */
	public function getForm($need = TRUE)
	{
		return $this->getComponent('form', $need);
	}


	/**
	 * Generates filter controls and checker's checkbox controls
	 * @param  AppForm
	 * @return void
	 */
	protected function regenerateFormControls()
	{
		$form = $this->getForm();

		// regenerate checker's checkbox controls
		if ($this->hasOperations()) {
			$values = $form->getValues();

			$form->removeComponent($form['checker']);
			$sub = $form->addContainer('checker');
			foreach ($this->getRows() as $row) {
				$sub->addCheckbox($row[$this->keyName], $row[$this->keyName]);
			}

			if (!empty($values['checker'])) {
				$form->setDefaults(array('checker' => $values['checker']));
			}
		}

		// for selectbox filter controls update values if was filtered over column
		if ($this->hasFilters()) {
			parse_str($this->filters, $list);

			foreach ($this->getFilters() as $filter) {
				if ($filter instanceof SelectboxFilter) {
					$filter->generateItems();
				}

				if ($this->filters === $this->defaultFilters && ($filter->value !== NULL || $filter->value !== '')) {
					if (!in_array($filter->getName(), array_keys($list))) $filter->value = NULL;
				}
			}
		}

		// page input & items selectbox
		$form['page']->setValue($this->paginator->page); // intentionally page from paginator
		$form['items']->setValue($this->paginator->itemsPerPage);
	}


	/**
	 * Allows operations and adds checker (column filled by checkboxes).
	 * @param  array  list of operations (selectbox items)
	 * @param  mixed  valid callback handler which provides rutines from $operations
	 * @param  string column name used to identifies each item/record in data grid (name of primary key of table/query from data source is recomended)
	 * @return void
	 */
	public function allowOperations(array $operations, $callback = NULL, $key = NULL)
	{
		$this->operations = $operations;

		if ($key != NULL && $this->keyName == NULL) {
			$this->keyName = $key;
		}
		if ($callback != NULL && $this->onOperationSubmit == NULL) {
			 $this->setOnOperationSubmit($callback);
		}
	}


	/**
	 * Generates columns from datasource.
	 * @return void
	 */
	protected function generateColumns()
	{
		if ($this->hasColumns('ActionColumn')) {
			$columns = $this->getColumns('ActionColumn');
			foreach ($columns as $column) {
				unset($this[$column->getName()]);
			}
		}

		$ds = clone $this->dataSource;
		$row = $ds->select('*')->applyLimit(1)->fetch();
		$keys = array_keys((array)$row);
		foreach ($keys as $key) $this->addColumn($key);

		if (isset($columns)) {
			foreach ($columns as $column) {
				$this[$column->getName()] = $column;
				$this->setCurrentActionColumn($column);
			}
		}
	}



	/********************* component factories *********************/



	/**
	 * Adds column of textual values.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  int     maximum number of dislayed characters
	 * @return TextColumn
	 */
	public function addColumn($name, $caption = NULL, $maxLength = NULL)
	{
		return $this[$name] = new TextColumn($caption, $maxLength);
	}


	/**
	 * Adds column of numeric values.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  int     number of digits after the decimal point
	 * @return NumericColumn
	 */
	public function addNumericColumn($name, $caption = NULL, $precision = 2)
	{
		return $this[$name] = new NumericColumn($caption, $precision);
	}


	/**
	 * Adds column of date-represented values.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  string  date format
	 * @return DateColumn
	 */
	public function addDateColumn($name, $caption = NULL, $format = '%x')
	{
		return $this[$name] = new DateColumn($caption, $format);
	}


	/**
	 * Adds column of boolean values (represented by checkboxes).
	 * @param  string  control name
	 * @param  string  column label
	 * @return CheckboxColumn
	 */
	public function addCheckboxColumn($name, $caption = NULL)
	{
		return $this[$name] = new CheckboxColumn($caption);
	}


	/**
	 * Adds column of graphical images.
	 * @param  string  control name
	 * @param  string  column label
	 * @return ImageColumn
	 */
	public function addImageColumn($name, $caption = NULL)
	{
		return $this[$name] = new ImageColumn($caption);
	}


	/**
	 * Adds column which provides moving entries up or down.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  string  destination or signal to handler which do the move rutine
	 * @param  array   textual labels for generated links
	 * @param  bool    use ajax? (add class DataGridColumn::$ajaxClass into generated link)
	 * @return PositionColumn
	 */
	public function addPositionColumn($name, $caption = NULL, $destination = NULL, array $moves = NULL, $useAjax = TRUE)
	{
		return $this[$name] = new PositionColumn($caption, $destination, $moves);
	}


	/**
	 * Adds column which represents logic container for data grid actions.
	 * @param  string  control name
	 * @param  string  column label
	 * @param  bool
	 * @return ActionColumn
	 */
	public function addActionColumn($name, $caption = NULL, $setAsCurrent = TRUE)
	{
		$column = new ActionColumn($caption);

		if ($setAsCurrent) {
			$this->setCurrentActionColumn($column);
		}
		return $this[$name] = $column;
	}


	/**
	 * @param  ActionColumn
	 * @return void
	 */
	public function setCurrentActionColumn(ActionColumn $column)
	{
		$this->currentActionColumn = $column;
	}


	/**
	 * Action factory.
	 * @param  string  textual title
	 * @param  string  textual link destination
	 * @param  Html    element which is added to a generated link
	 * @param  bool    use ajax? (add class self::$ajaxClass into generated link)
	 * @param  mixed   generate link with argument? (if yes you can specify name of parameter
	 * 				   otherwise variable DataGrid::$keyName will be used and must be defined)
	 * @return DataGridAction
	 */
	public function addAction($title, $signal, $icon = NULL, $useAjax = FALSE, $key = DataGridAction::WITH_KEY)
	{
		if (!$this->hasColumns('ActionColumn')) {
			throw new InvalidStateException('No ActionColumn defined. Use DataGrid::addActionColumn before you add actions.');
		}

		return $this->currentActionColumn->addAction($title, $signal, $icon, $useAjax, $key);
	}



	/********************* translator ********************/



	/**
	 * Sets translate adapter.
	 * @param  ITranslator
	 * @return void
	 */
	public function setTranslator(ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}


	/**
	 * Returns translate adapter.
	 * @return ITranslator|NULL
	 */
	final public function getTranslator()
	{
		return $this->translator;
	}


	/**
	 * Returns translated string.
	 * @param  string
	 * @return string
	 */
	public function translate($s)
	{
		$args = func_get_args();
		return $this->translator === NULL ? $s : call_user_func_array(array($this->getTranslator(), 'translate'), $args);
	}



	/********************* interface \ISignalReceiver *********************/



	/**
	 * Checks if component is signal receiver.
	 * @param  string  signal name
	 * @return bool
	 */
	public function isSignalReceiver($signal = TRUE)
	{
		if ($signal == 'submit') {
			return $this->receivedSignal === 'submit';
		} else {
			return $this->getPresenter()->isSignalReceiver($this, $signal);
		}

		// TODO: zatim musi byt reseno takto protoze nize uvedene reseni neni funkcni
		// TODO: dokud nebude vyreseno toto tema http://forum.nettephp.com/cs/1813-metoda-issignalreceiver-v-komponentach
		// TODO: pak odstranit i promennou receivedSignal
		//return $this->getPresenter()->isSignalReceiver($signal == 'submit' ? $this->getForm() : $this, $signal);
	}



	/********************* backend *********************/



	/**
	 * @return SessionNamespace
	 */
	protected function getStateSession()
	{
		return $this->getSession()->getNamespace('Nette.Extras.DataGrid/' . $this->getName() . '/states');
	}


	/**
	 * @return Session
	 */
	protected function getSession()
	{
		return Environment::getSession();
	}


	/**
	 * Renders table grid and return as string.
	 * @return string
	 */
	public function __toString()
	{
		$s = call_user_func_array(array($this->getRenderer(), 'render'), array($this));
		return mb_convert_encoding($s, 'HTML-ENTITIES', 'UTF-8');
	}
}