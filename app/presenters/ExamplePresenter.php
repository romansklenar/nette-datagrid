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
		$this->template->baseGrid = $this->getComponent('baseGrid');
		$this->template->officesGrid = $this->getComponent('officesGrid');
		$this->template->customersGrid = $this->getComponent('customersGrid');
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
			$grid->bindDataTable($model->getCustomerAndOrderInfo(), $model->table);
			
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
			$grid->addNumericColumn('creditLimit', 'Size', 0);
			
			$this->addComponent($grid, $name);
			return;
			
			
		case 'officesGrid':
			$model = new DatagridModel('offices');
			$grid = new DataGrid;
			$grid->bindDataTable($model->findAll($model->table)->orderBy('position')->toDataSource()); // binds DibiDataSource
			$grid->keyName = 'officeCode'; // for actions or operations
			
			$pos = array('up' => 'Move up', 'down' => 'Move down');
			$grid->addPositionColumn('position', '# Position', 'positionMove!', $pos)->addFilter();
			$grid->addColumn('phone', 'Phone')->addFilter();
			$grid->addColumn('addressLine1', 'Address')->addFilter();
			$grid->addColumn('city', 'City')->addFilter();
			$grid->addColumn('country', 'Country')->addSelectboxFilter();
			$grid->addNumericColumn('postalCode', 'Postal code')->addFilter();
			
			$grid->addActionColumn('Actions');
			$icon = Html::el('span')->setHtml('&nbsp;');
			$grid->addAction('New entry', 'Office:new', clone $icon->class('icon icon-add'), FALSE, DataGridAction::WITHOUT_KEY);
			$grid->addAction('Edit', 'Office:edit', clone $icon->class('icon icon-edit'));
			$grid->addAction('Delete', 'Office:delete', clone $icon->class('icon icon-del'));
			
			$this->addComponent($grid, $name);
			return;
			
			
		case 'customersGrid':			
			$model = new DatagridModel('customers');			
			$grid = new DataGrid;
			$grid->setRenderer(new CustomDataGridRenderer);
			
			$grid->rowsPerPage = 10; // display 10 rows per page
			$grid->bindDataTable($model->getCustomerAndOrderInfo(), $model->table);
			
			$grid->multiOrder = FALSE; // order by one column only
			
			$operations = array('delete' => 'delete', 'deal' => 'deal', 'print' => 'print', 'forward' => 'forward'); // define operations
			// in czech for example: $operations = array('delete' => 'smazat', 'deal' => 'vyřídit', 'print' => 'tisk', 'forward' => 'předat');
			$callback = array($this, 'gridOperationHandler');
			$grid->allowOperations($operations, $callback, 'customerNumber'); // allows checkboxes to do operations with more rows
			
			
			/**** add some columns ****/
			
			$grid->addColumn('customerName', 'Name');
			$grid->addColumn('contactLastName', 'Surname'); // ->addFilter();
			$grid->addColumn('addressLine1', 'Address')->getHeaderPrototype()->style('width: 180px');
			$grid->addColumn('city', 'City'); // ->addSelectboxFilter();
			$grid->addColumn('country', 'Country'); // ->addSelectboxFilter();
			$grid->addColumn('postalCode', 'Postal code'); // ->addCheckboxFilter();
			$caption = Html::el('span')->setText('O')->title('Has orders?')->class('link');
			$grid->addCheckboxColumn('orders', $caption)->getHeaderPrototype()->style('text-align: center');
			$grid->addDateColumn('orderDate', 'Date', '%m/%d/%Y'); // czech format: '%d.%m.%Y'
			$grid->addColumn('status', 'Status');
			$grid->addNumericColumn('creditLimit', 'Size', 0);
			
			
			/**** add some filters ****/
			
			$grid['customerName']->addFilter();
			$grid['contactLastName']->addFilter();
			$grid['addressLine1']->addFilter();
			$grid['city']->addSelectboxFilter();
			$grid['country']->addSelectboxFilter();
			$grid['postalCode']->addFilter();
			$grid['orders']->addSelectboxFilter(array('?' => '?', '0' => "Don't have", '1' => "Have"), TRUE);
			$grid['orderDate']->addDateFilter();
			$grid['status']->addSelectboxFilter();
			$grid['creditLimit']->addFilter();
			
			
			/**** column content affecting ****/
			
			// by css styling
			$grid['orderDate']->getCellPrototype()->style('text-align: center');
			
			// by replacement of given pattern
			$el = Html::el('span')->style('margin: 0 auto')->setHtml('&nbsp;');
			$grid['status']->replacement['Shipped'] = clone $el->class("icon icon-shipped")->title("Shipped");
			$grid['status']->replacement['Resolved'] = clone $el->class("icon icon-resolved")->title("Resolved");
			$grid['status']->replacement['Cancelled'] = clone $el->class("icon icon-cancelled")->title("Cancelled");
			$grid['status']->replacement[''] = clone $el->class("icon icon-no-orders")->title("Without orders");
			
			// by callback(s)
			$grid['creditLimit']->formatCallback[] = 'TemplateHelpers::bytes';
			
			
			/**** add some actions ****/
			
			$grid->addActionColumn('Actions')->getHeaderPrototype()->style('text-align: center');
			$grid['actions']->getCellPrototype()->style('text-align: center');
			$icon = Html::el('span')->setHtml('&nbsp;');
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
				
		// was submitted?
		if ($form->isSubmitted() && $form->isValid()) {
			$values = $form->getValues();
			
			if ($button->getName() === 'operationSubmit') {
				$action = $values['operations'];				
			} else {
				throw new InvalidArgumentException("Unknown submit button '" . $button->getName() . "'.");
			}
			
			$rows = array();
			foreach ($values['checker'] as $k => $v) {
				if ($v) $rows[] = $k; 
			}
			
			if (count($rows) > 0) {
				$rows = implode(',', $rows);
				$this->presenter->flashMessage("Group operations $action over rows $rows succesfully done.", 'success');
			} else {
				$this->presenter->flashMessage("No rows selected for group operations.", 'warning');
			}
		}
		
		
		$this->invalidateControl('flashMessage');
		$this->getComponent('customersGrid')->invalidateControl('grid');
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
	
	
	/**
	 * Custom signal positionMove! handler (given as parameter for PositionColumn).
	 * @param  string  which item of datagrid
	 * @param  string  move which direction
	 * @return void
	 */
	public function handlePositionMove($key, $dir)
	{
		// TODO: write more sophisticated handler :)
		$model = new DatagridModel('offices');
		
		if ($dir == 'down') {
			$old = $model->connection->query('SELECT [officeCode] FROM [offices] WHERE [position] = %i', $key)->fetchSingle();
			$new = $model->connection->query('SELECT [officeCode] FROM [offices] WHERE [position] = %i', $key+1)->fetchSingle();
			$model->connection->query('UPDATE [offices] SET [position] = %i', $key+1, ' WHERE [officeCode] = %i', $old['officeCode']);
			$model->connection->query('UPDATE [offices] SET [position] = %i', $key, ' WHERE [officeCode] = %i', $new['officeCode']);
			
		} else {
			$old = $model->connection->query('SELECT [officeCode] FROM [offices] WHERE [position] = %i', $key)->fetchSingle();
			$new = $model->connection->query('SELECT [officeCode] FROM [offices] WHERE [position] = %i', $key-1)->fetchSingle();
			$model->connection->query('UPDATE [offices] SET [position] = %i', $key-1, ' WHERE [officeCode] = %i', $old['officeCode']);
			$model->connection->query('UPDATE [offices] SET [position] = %i', $key, ' WHERE [officeCode] = %i', $new['officeCode']);		}
		
		$this->flashMessage('Succesfully moved.', 'info');
		$this->invalidateControl('flashMessage');
		$this->getComponent('officesGrid')->invalidateControl('grid');
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
}