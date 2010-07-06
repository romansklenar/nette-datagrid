<?php

use Nette\Web\Html,
	Nette\Environment;

/**
 * DataGrid demo presenter.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class ExamplePresenter extends BasePresenter
{

	protected function createComponentBaseGrid()
	{
		$model = new DatagridModel;

		// if no columns are defined, takes all cols from given data source
		$grid = new DataGrid\DataGrid;
		$grid->bindDataTable($model->getOrdersInfo());

		return $grid;
	}

	protected function createComponentOfficesGrid()
	{
		$model = new DatagridModel;

		$grid = new DataGrid\DataGrid;
		$grid->bindDataTable($model->getOfficesInfo()->orderBy('position')); // binds DibiDataSource
		$grid->keyName = 'officeCode'; // for actions or operations
		$grid->disableOrder = TRUE;

		$pos = array('up' => 'Move up', 'down' => 'Move down');
		$grid->addPositionColumn('position', 'Position', 'positionMove!', $pos)->addFilter();
		$grid->addColumn('phone', 'Phone')->addFilter();
		$grid->addColumn('addressLine1', 'Address')->addFilter();
		$grid->addColumn('city', 'City')->addFilter();
		$grid->addColumn('country', 'Country')->addSelectboxFilter()->translateItems(FALSE);
		$grid->addColumn('postalCode', 'Postal code')->addFilter();
		$grid->addCheckboxColumn('hasEmployees', 'Has employees')
			->addSelectboxFilter(array('0' => "Don't have", '1' => "Have"), TRUE);
		$grid->addNumericColumn('employeesCount', 'Employees count')->getCellPrototype()->addStyle('text-align: center');
		$grid['employeesCount']->addFilter();

		$grid->addActionColumn('Actions');
		$icon = Html::el('span');
		$grid->addAction('New entry', 'Office:new', clone $icon->class('icon icon-add'), FALSE, DataGrid\Action::WITHOUT_KEY);
		$grid->addAction('Edit', 'Office:edit', clone $icon->class('icon icon-edit'));
		$grid->addAction('Delete', 'Office:delete', clone $icon->class('icon icon-del'));

		return $grid;
	}

	protected function createComponentOrdersGrid()
	{
		$model = new DatagridModel;
		$grid = new DataGrid\DataGrid;

		$translator = new GettextTranslator(Environment::expand('%templatesDir%/customersGrid.cs.mo'));
		$grid->setTranslator($translator);

		$renderer = new DataGrid\Renderer;
		$renderer->paginatorFormat = '%input%'; // customize format of paginator
		$renderer->onCellRender[] = array($this, 'ordersGridOnCellRendered');
		$grid->setRenderer($renderer);

		$grid->itemsPerPage = 10; // display 10 rows per page
		$grid->displayedItems = array('all', 10, 20, 50); // items per page selectbox items
		$grid->rememberState = TRUE;
		$grid->timeout = '+ 7 days'; // change session expiration after 7 days
		$grid->bindDataTable($model->getOrdersInfo());
		$grid->multiOrder = FALSE; // order by one column only

		$operations = array('delete' => 'delete', 'deal' => 'deal', 'print' => 'print', 'forward' => 'forward'); // define operations
		// in czech for example: $operations = array('delete' => 'smazat', 'deal' => 'vyřídit', 'print' => 'tisk', 'forward' => 'předat');
		// or you can left translate values by translator adapter
		$callback = array($this, 'gridOperationHandler');
		$grid->allowOperations($operations, $callback, 'orderNumber'); // allows checkboxes to do operations with more rows


		/**** add some columns ****/

		$grid->addColumn('customerName', 'Customer');
		$grid->addColumn('addressLine1', 'Address')->getHeaderPrototype()->addStyle('width: 180px');
		$grid->addColumn('city', 'City');
		$grid->addColumn('country', 'Country');
		$caption = Html::el('span')->setText('P')->title('Number of products on order')->class('link');
		$grid->addNumericColumn('productsCount', $caption)->getCellPrototype()->addStyle('text-align: center');
		$grid->addDateColumn('orderDate', 'Date', '%m/%d/%Y'); // czech format: '%d.%m.%Y'
		$grid->addColumn('status', 'Status');
		$grid->addColumn('creditLimit', 'Credit')->getCellPrototype()->addStyle('text-align: center');


		/**** add some filters ****/

		$grid['customerName']->addFilter();
		$grid['addressLine1']->addFilter();
		$grid['city']->addSelectboxFilter()->translateItems(FALSE);
		$grid['country']->addSelectboxFilter()->translateItems(FALSE);
		$grid['productsCount']->addFilter();
		$grid['orderDate']->addDateFilter();
		$grid['status']->addSelectboxFilter();
		$grid['creditLimit']->addFilter();


		/**** default sorting and filtering ****/

		$grid['orderDate']->addDefaultSorting('desc');
		$grid['productsCount']->addDefaultFiltering('>2');

		/**** column content affecting ****/

		// by css styling
		$grid['orderDate']->getCellPrototype()->addStyle('text-align: center');
		$grid['status']->getHeaderPrototype()->addStyle('width: 60px');
		$grid['addressLine1']->getHeaderPrototype()->addStyle('width: 150px');
		$grid['city']->getHeaderPrototype()->addStyle('width: 90px');

		// by replacement of given pattern
		$el = Html::el('span')->addStyle('margin: 0 auto');
		$grid['status']->replacement['Shipped'] = clone $el->class("icon icon-shipped")->title("Shipped");
		$grid['status']->replacement['Resolved'] = clone $el->class("icon icon-resolved")->title("Resolved");
		$grid['status']->replacement['Cancelled'] = clone $el->class("icon icon-cancelled")->title("Cancelled");
		$grid['status']->replacement['On Hold'] = clone $el->class("icon icon-hold")->title("On Hold");
		$grid['status']->replacement['In Process'] = clone $el->class("icon icon-process")->title("In Process");
		$grid['status']->replacement['Disputed'] = clone $el->class("icon icon-disputed")->title("Disputed");
		$grid['status']->replacement[''] = clone $el->class("icon icon-no-orders")->title("Without orders");

		// by callback(s)
		$grid['creditLimit']->formatCallback[] = 'Helpers::currency';


		/**** add some actions ****/

		$grid->addActionColumn('Actions')->getHeaderPrototype()->addStyle('width: 98px');
		$icon = Html::el('span');
		$grid->addAction('Copy', 'Customer:copy', clone $icon->class('icon icon-copy'));
		$grid->addAction('Detail', 'Customer:detail', clone $icon->class('icon icon-detail'));
		$grid->addAction('Edit', 'Customer:edit', clone $icon->class('icon icon-edit'));
		$grid->addAction('Delete', 'Customer:delete', clone $icon->class('icon icon-del'));

		return $grid;
	}



	/**
	 * Custom group operations handler.
	 * @param  SubmitButton
	 * @return void
	 */
	public function gridOperationHandler(Nette\Forms\SubmitButton $button)
	{
		// how to findout which checkboxes in checker was checked?  $values['checker']['ID'] => bool(TRUE)
		$form = $button->getParent();
		$grid = $this->getComponent('ordersGrid');

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
	public function ordersGridOnCellRendered(Nette\Web\Html $cell, $column, $value)
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
		$model = new DatagridModel('offices');
		$model->officePositionMove($key, $dir);

		$this->getComponent('officesGrid')->flashMessage('Succesfully moved.', 'info');
		$this->getComponent('officesGrid')->invalidateControl();
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
}