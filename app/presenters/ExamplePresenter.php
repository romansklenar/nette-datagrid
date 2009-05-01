<?php


class ExamplePresenter extends BasePresenter
{


	
	public function renderDefault()
	{
		$this->template->baseGrid = $this->getComponent('baseGrid');
		$this->template->customersGrid = $this->getComponent('customersGrid');
		$this->template->officesGrid = $this->getComponent('officesGrid');
	}
	
	
	/**
	 * Component factory
	 * @see Nette/ComponentContainer#createComponent()
	 */
	protected function createComponent($name)
	{
		switch ($name) {
		case 'baseGrid':			
			$model = new DatagridModel('Customers');
			$grid = new DataGrid;
			$grid->bindDataTable($model->getCustomerAndOrderInfo(), $model->table);			
			
			// if no columns are defined, takes all cols from given data source
			$grid->addColumn('customerName', 'Jméno');
			$grid->addColumn('contactLastName', 'Příjmení');
			$grid->addColumn('addressLine1', 'Adresa');
			$grid->addColumn('city', 'Město');
			$grid->addColumn('country', 'Stát');
			$grid->addColumn('postalCode', 'PSČ');
			$grid->addCheckboxColumn('orders', 'Objednávek');
			$grid->addDateColumn('orderDate', 'Datum', '%d.%m.%Y');
			$grid->addColumn('status', 'Status');
			$grid->addNumericColumn('creditLimit', 'Velikost', 0);
			
			$this->addComponent($grid, $name);
			return;
			
		case 'customersGrid':			
			$model = new DatagridModel('Customers');			
			$grid = new DataGrid;
			
			$grid->rowsPerPage = 10; // display 10 rows per page
			$grid->bindDataTable($model->getCustomerAndOrderInfo(), $model->table);
			
			$grid->multiOrder = FALSE; // order by one column only
			
			$operations = array('del' => 'smazat', 'deal' => 'vyřídit', 'print' => 'tisk', 'forward' => 'předat'); // define operations
			$callback = array($this, 'gridOperationHandler');
			$grid->allowOperations($operations, $callback, 'customerNumber'); // allows checkboxes to do operations with more rows
			
			
			/**** pridani sloupcu ****/
			
			$grid->addColumn('customerName', 'Jméno');
			$grid->addColumn('contactLastName', 'Příjmení'); // ->addFilter();
			$grid->addColumn('addressLine1', 'Adresa')->getHeaderPrototype()->style('width: 180px');
			$grid->addColumn('city', 'Město'); // ->addSelectboxFilter();
			$grid->addColumn('country', 'Stát'); // ->addSelectboxFilter();
			$grid->addColumn('postalCode', 'PSČ'); // ->addCheckboxFilter();
			$caption = Html::el('span')->setText('O')->title('Objednávek')->class('link');
			$grid->addCheckboxColumn('orders', $caption)->getHeaderPrototype()->style('text-align: center');
			$grid->addDateColumn('orderDate', 'Datum', '%d.%m.%Y');
			$grid->addColumn('status', 'Status');
			$grid->addNumericColumn('creditLimit', 'Velikost', 0);
			
			
			/**** pridani filtru ****/
			
			$grid['customerName']->addFilter();
			$grid['contactLastName']->addFilter();
			$grid['addressLine1']->addFilter();
			$grid['city']->addSelectboxFilter();
			$grid['country']->addSelectboxFilter();
			$grid['postalCode']->addFilter();
			$grid['orders']->addSelectboxFilter(array('?' => '?', '0' => 'Nemá', '1' => 'Má'), TRUE);
			$grid['orderDate']->addDateFilter();
			$grid['status']->addSelectboxFilter();
			$grid['creditLimit']->addFilter();
			
			
			/**** ovlivnovani vypisu ****/
			
			// css stylovani
			$grid['orderDate']->getCellPrototype()->style('text-align: center');
			
			// nahrazovani zadanych hodnot
			$el = Html::el('span')->style('margin: 0 auto')->setHtml('&nbsp;');
			$grid['status']->replacement['Shipped'] = clone $el->class("icon icon-shipped")->title("Dodáno");
			$grid['status']->replacement['Resolved'] = clone $el->class("icon icon-resolved")->title("Vyřešeno");
			$grid['status']->replacement['Cancelled'] = clone $el->class("icon icon-cancelled")->title("Zrušeno");
			$grid['status']->replacement[''] = clone $el->class("icon icon-no-orders")->title("Bez objednávek");
			
			// nahrazeni pomoci callbacku
			$grid['creditLimit']->formatCallback[] = 'TemplateHelpers::bytes';
			
			
			/**** pridani akci ****/
			
			$grid->addActionColumn('Akce')->getHeaderPrototype()->style('width: 100px');
			$icon = Html::el('span')->setHtml('&nbsp;');
			$grid->addAction('Kopírovat', 'Zakaznik:kopirovat', clone $icon->class('icon icon-copy'));
			$grid->addAction('Detail', 'Zakaznik:detail', clone $icon->class('icon icon-detail'));
			$grid->addAction('Upravit', 'Zakaznik:uprav', clone $icon->class('icon icon-edit'));
			$grid->addAction('Smazat', 'Zakaznik:smazat', clone $icon->class('icon icon-del'));			
			
			$this->addComponent($grid, $name);
			return;
			
		case 'officesGrid':
			$model = new DatagridModel('Offices');
			$grid = new DataGrid;
			$grid->bindDataTable($model->findAll($model->table)->orderBy('position')->toDataSource()/*, $model->table*/); // binds DibiDataSource
			$grid->keyName = 'officeCode'; 
			
			$pos = array('up' => 'Posunout nahoru', 'down' => 'Posunout dolů');
			$grid->addPositionColumn('position', '# Pozice', 'positionMove!', $pos)->addFilter();
			$grid->addColumn('phone', 'Telefon')->addFilter();
			$grid->addColumn('addressLine1', 'Adresa')->addFilter();
			$grid->addColumn('city', 'Město')->addFilter();
			$grid->addColumn('country', 'Země')->addSelectboxFilter();
			$grid->addCheckboxColumn('postalCode', 'PSČ')->addFilter();
			
			$grid->addActionColumn('Akce');
			$icon = Html::el('span')->setHtml('&nbsp;');
			$grid->addAction('Nový záznam', 'Kancelar:nova', clone $icon->class('icon icon-add'), FALSE, DataGridAction::WITHOUT_KEY);
			$grid->addAction('Upravit', 'Kancelar:uprav', clone $icon->class('icon icon-edit'));
			$grid->addAction('Smazat', 'Kancelar:smazat', clone $icon->class('icon icon-del'));
			
			$this->addComponent($grid, $name);
			return;
			
		default:
			parent::createComponent($name);
			return;
		}
	}
	
	
	/**
	 * Table-select form submit handler
	 * @param $button SubmitButton
	 * @return void
	 */
	public function gridOperationHandler(SubmitButton $button)
	{
		// zjisteni, ktere checkboxy byly zaskrtnuty  $values['checker']['ID']; => bool(TRUE)
		$form = $button->getParent();
				
		// byl odeslán?
		if ($form->isSubmitted()/* && $form->isValid()*/) {
			$values = $form->getValues();
			
			if ($button->getName() === 'topSubmit') {
				$action = $values['topAction'];				
			} elseif ($button->getName() === 'bottomSubmit') {
				$action = $values['bottomAction'];
			} else {
				throw new InvalidArgumentException("Unknown submit button '" . $button->getName() . "'.");
			}
			
			$rows = array();
			foreach ($values['checker'] as $k => $v) {
				if ($v) $rows[] = $k; 
			}
			
			if (count($rows) > 0) {
				$rows = implode(',', $rows);
				$this->presenter->flashMessage("Hromadná operace $action nad řádky $rows úspěšně provedena.", 'success');
			} else {
				$this->presenter->flashMessage("Nebyly vybrány žádné řádky k provedení hromadné operace.", 'warning');
			}

		} else {
			// první zobrazení, nastavíme výchozí hodnoty
			//$form->setDefaults($defaults);
		}
		
		
		$this->invalidateControl('flashMessage');
		$this->getComponent('customersGrid')->invalidateControl('grid');
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}
	
	
	public function handlePositionMove($key, $dir)
	{
		// TODO: napsat na presun lepsi handler
		$model = new DatagridModel('Offices');
		
		if ($dir == 'down') {
			$old = $model->connection->query('SELECT [officeCode] FROM [Offices] WHERE [position] = %i', $key)->fetchSingle();
			$new = $model->connection->query('SELECT [officeCode] FROM [Offices] WHERE [position] = %i', $key+1)->fetchSingle();
			$model->connection->query('UPDATE [Offices] SET [position] = %i', $key+1, ' WHERE [officeCode] = %i', $old['officeCode']);
			$model->connection->query('UPDATE [Offices] SET [position] = %i', $key, ' WHERE [officeCode] = %i', $new['officeCode']);
			
			//$model->connection->query('UPDATE `table` SET ', $arr, 'WHERE `id`=%i', $x
		} else {
			$old = $model->connection->query('SELECT [officeCode] FROM [Offices] WHERE [position] = %i', $key)->fetchSingle();
			$new = $model->connection->query('SELECT [officeCode] FROM [Offices] WHERE [position] = %i', $key-1)->fetchSingle();
			$model->connection->query('UPDATE [Offices] SET [position] = %i', $key-1, ' WHERE [officeCode] = %i', $old['officeCode']);
			$model->connection->query('UPDATE [Offices] SET [position] = %i', $key, ' WHERE [officeCode] = %i', $new['officeCode']);		}
		
		$this->flashMessage('Úspěšně přesunuto.', 'info');
		$this->invalidateControl('flashMessage');
		$this->getComponent('officesGrid')->invalidateControl('grid');
		if (!$this->presenter->isAjax()) $this->presenter->redirect('this');
	}


}


