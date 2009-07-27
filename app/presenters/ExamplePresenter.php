<?php

/**
 * DataGrid demo presenter.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class ExamplePresenter extends BasePresenter
{
	/**
	 * Common render method.
	 * @return void
	 */
	public function renderDefault()
	{
		Debug::timer('grids-creating');

		$this->template->baseGrid = $this->getComponent('baseGrid');
		$this->template->officesGrid = $this->getComponent('officesGrid');
		$this->template->customersGrid = $this->getComponent('customersGrid');

		Environment::setVariable('creating', Debug::timer('grids-creating') * 1000);
	}


	/**
	 * Component factory
	 * @see Nette/ComponentContainer#createComponent()
	 */
	protected function createComponent($name)
	{
		switch ($name) {
		case 'baseGrid':
			$model = new DatagridModel('customers');
			$grid = new DataGrid;
			$grid->bindDataTable($model->getCustomerAndOrderInfo());

			// if no columns are defined, takes all cols from given data source
			$grid->addColumn('customerName', 'Name');
			$grid->addColumn('contactLastName', 'Surname');
			$grid->addColumn('addressLine1', 'Address');
			$grid->addColumn('city', 'City');
			$grid->addColumn('country', 'Country');
			$grid->addColumn('postalCode', 'Postal code');
			$grid->addCheckboxColumn('orders', 'Has orders');
			$grid->addDateColumn('orderDate', 'Date', '%m/%d/%Y');
			$grid->addColumn('status', 'Status');
			$grid->addNumericColumn('creditLimit', 'Credit', 0);

			$this->addComponent($grid, $name);
			return;


		case 'officesGrid':
			$model = new DatagridModel('offices');
			$grid = new DataGrid;
			$grid->bindDataTable($model->findAll($model->table)->orderBy('position')->toDataSource()); // binds DibiDataSource
			$grid->keyName = 'officeCode'; // for actions or operations
			$grid->disableOrder = TRUE;

			$pos = array('up' => 'Move up', 'down' => 'Move down');
			$grid->addPositionColumn('position', 'Position', 'positionMove!', $pos)->addFilter();
			$grid->addColumn('phone', 'Phone')->addFilter();
			$grid->addColumn('addressLine1', 'Address')->addFilter();
			$grid->addColumn('city', 'City')->addFilter();
			$grid->addColumn('country', 'Country')->addSelectboxFilter()->translateItems(FALSE);
			$grid->addColumn('postalCode', 'Postal code')->addFilter();

			$grid->addActionColumn('Actions');
			$icon = Html::el('span');
			$grid->addAction('New entry', 'Office:new', clone $icon->class('icon icon-add'), FALSE, DataGridAction::WITHOUT_KEY);
			$grid->addAction('Edit', 'Office:edit', clone $icon->class('icon icon-edit'));
			$grid->addAction('Delete', 'Office:delete', clone $icon->class('icon icon-del'));

			$this->addComponent($grid, $name);
			return;


		case 'customersGrid':
			$model = new DatagridModel('customers');
			$grid = new DataGrid;

			$translator = new Translator(Environment::expand('%templatesDir%/customersGrid.cs.mo'));
			$grid->setTranslator($translator);

			$renderer = new DataGridRenderer;
			$renderer->paginatorFormat = '%input%'; // customize format of paginator
			$renderer->onCellRender[] = array($this, 'customersGridOnCellRendered');
			$grid->setRenderer($renderer);

			$grid->itemsPerPage = 10; // display 10 rows per page
			$grid->displayedItems = array('all', 10, 20, 50); // items per page selectbox items
			$grid->rememberState = TRUE;
			$grid->timeout = '+ 7 days'; // change session expiration after 7 days
			$grid->bindDataTable($model->getCustomerAndOrderInfo());
			$grid->multiOrder = FALSE; // order by one column only

			$operations = array('delete' => 'delete', 'deal' => 'deal', 'print' => 'print', 'forward' => 'forward'); // define operations
			// in czech for example: $operations = array('delete' => 'smazat', 'deal' => 'vyřídit', 'print' => 'tisk', 'forward' => 'předat');
			// or you can left translate values by translator adapter
			$callback = array($this, 'gridOperationHandler');
			$grid->allowOperations($operations, $callback, 'customerNumber'); // allows checkboxes to do operations with more rows


			/**** add some columns ****/

			$grid->addColumn('customerName', 'Name');
			$grid->addColumn('contactLastName', 'Surname');
			$grid->addColumn('addressLine1', 'Address')->getHeaderPrototype()->style('width: 180px');
			$grid->addColumn('city', 'City');
			$grid->addColumn('country', 'Country');
			$grid->addColumn('postalCode', 'Postal code');
			$caption = Html::el('span')->setText('O')->title('Has orders?')->class('link');
			$grid->addCheckboxColumn('orders', $caption)->getHeaderPrototype()->style('text-align: center');
			$grid->addDateColumn('orderDate', 'Date', '%m/%d/%Y'); // czech format: '%d.%m.%Y'
			$grid->addColumn('status', 'Status');
			$grid->addNumericColumn('creditLimit', 'Credit', 0);


			/**** add some filters ****/

			$grid['customerName']->addFilter();
			$grid['contactLastName']->addFilter();
			$grid['addressLine1']->addFilter();
			$grid['city']->addSelectboxFilter()->translateItems(FALSE);
			$grid['country']->addSelectboxFilter()->translateItems(FALSE);
			$grid['postalCode']->addFilter();
			$grid['orders']->addSelectboxFilter(array('?' => '?', '0' => "Don't have", '1' => "Have"), TRUE);
			$grid['orderDate']->addDateFilter();
			$grid['status']->addSelectboxFilter();
			$grid['creditLimit']->addFilter();


			/**** default sorting and filtering ****/

			$grid['city']->addDefaultSorting('asc');
			$grid['contactLastName']->addDefaultSorting('asc');
			$grid['orders']->addDefaultFiltering(TRUE);
			$grid['country']->addDefaultFiltering('USA');

			/**** column content affecting ****/

			// by css styling
			$grid['orderDate']->getCellPrototype()->style('text-align: center');

			// by replacement of given pattern
			$el = Html::el('span')->style('margin: 0 auto');
			$grid['status']->replacement['Shipped'] = clone $el->class("icon icon-shipped")->title("Shipped");
			$grid['status']->replacement['Resolved'] = clone $el->class("icon icon-resolved")->title("Resolved");
			$grid['status']->replacement['Cancelled'] = clone $el->class("icon icon-cancelled")->title("Cancelled");
			$grid['status']->replacement[''] = clone $el->class("icon icon-no-orders")->title("Without orders");

			// by callback(s)
			$grid['creditLimit']->formatCallback[] = 'Helpers::currency';


			/**** add some actions ****/

			$grid->addActionColumn('Actions')->getHeaderPrototype()->style('width: 98px');
			$icon = Html::el('span');
			$grid->addAction('Copy', 'Customer:copy', clone $icon->class('icon icon-copy'));
			$grid->addAction('Detail', 'Customer:detail', clone $icon->class('icon icon-detail'));
			$grid->addAction('Edit', 'Customer:edit', clone $icon->class('icon icon-edit'));
			$grid->addAction('Delete', 'Customer:delete', clone $icon->class('icon icon-del'));


			$this->addComponent($grid, $name);
			return;


		default:
			parent::createComponent($name);
			return;
		}
	}


	/**
	 * Custom group operations handler.
	 * @param  SubmitButton
	 * @return void
	 */
	public function gridOperationHandler(SubmitButton $button)
	{
		// how to findout which checkboxes in checker was checked?  $values['checker']['ID'] => bool(TRUE)
		$form = $button->getParent();
		$grid = $this->getComponent('customersGrid');

		// was submitted?
		if ($form->isSubmitted() && $form->isValid()) {
			$values = $form->getValues();

			if ($button->getName() === 'operationSubmit') {
				$operation = $values['operations'];
			} else {
				throw new InvalidArgumentException("Unknown submit button '" . $button->getName() . "'.");
			}

			$rows = array();
			foreach ($values['checker'] as $k => $v) {
				if ($v) $rows[] = $k;
			}

			if (count($rows) > 0) {
				$msg = $grid->translate('Operation %2$s over row %3$s succesfully done.', count($rows), $grid->translate($operation), implode(', ', $rows));
				$grid->flashMessage($msg, 'success');
				$msg = $grid->translate('This is demo application only, changes will not be done.');
				$grid->flashMessage($msg, 'info');
			} else {
				$msg = $grid->translate('No rows selected.');
				$grid->flashMessage($msg, 'warning');
			}
		}

		$grid->invalidateControl();
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}


	/**
	 * 'customersGrid' onCellRender event.
	 * @param  Html
	 * @param  string
	 * @param  mixed
	 * @return Html
	 */
	public function customersGridOnCellRendered(Html $cell, $column, $value)
	{
		if ($column === 'creditLimit') {
			if ($value < 30000) $cell->addClass('money-low');
			elseif ($value >= 100000) $cell->addClass('money-high');
		}
		return $cell;
	}


	/**
	 * Custom signal positionMove! handler (given as parameter for PositionColumn).
	 * @param  string  which item of datagrid
	 * @param  string  move which direction
	 * @return void
	 */
	public function handlePositionMove($key, $dir)
	{
		// TODO: write your own more sophisticated handler ;) $model->officePositionMove($key, $dir)
		$model = new DatagridModel('offices');
		$model->officePositionMove($key, $dir);

		$this->getComponent('officesGrid')->flashMessage('Succesfully moved.', 'info');
		$this->getComponent('officesGrid')->invalidateControl();
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
}