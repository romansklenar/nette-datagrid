<?php



class DataGridRenderer extends Object implements IDataGridRenderer
{
	// TODO: udelat vlastni vykreslovac begin, error a end? ANO!
	/** @var array of HTML tags */
	public $wrappers = array(
		'form' => array(
			'container' => 'class=gridform',
			'errors' => TRUE,
		),

		'error' => array(
			'container' => 'ul class=error',
			'item' => 'li',
		),

		'grid' => array(
			'container' => 'table class=grid',
		),
		
		'row.header' => array(
			'container' => 'tr class=header',
			'cell' => array(
				'container' => 'th', // .checker, .action
			),
		),
		
		'row.filter' => array(
			'container' => 'tr class=filters',
			'cell' => array(
				'container' => 'td', // .action
			),
		),
		
		'row.content' => array(
			'container' => 'tr', // .alt, .selected
			'cell' => array(
				'container' => 'td', // .checker, .action
			),	
		),
		
		'row.footer' => array(
			'container' => 'tr class=footer',
			'cell' => array(
				'container' => 'th', // .action
			),
		),
	
	);

	/** @var DataGrid */
	protected $dataGrid;
	
	
	
	/**
	 * Provides complete datagrid rendering.
	 * @param  DataGrid
	 * @param  string
	 * @return string
	 */
	public function render(DataGrid $dataGrid, $mode = NULL)
	{
		if ($this->dataGrid !== $dataGrid) {
			$this->dataGrid = $dataGrid;
		}
		
		if (!$dataGrid->hasColumns()) {
			// auto-generate columns
			$row = $dataGrid->dataSource->select('*')->fetch();
			$keys = array_keys((array)$row);			
			foreach ($keys as $key) $dataGrid->addColumn($key);
		}

		$s = '';
		if  ($this->dataGrid->hasFilters() || $this->dataGrid->hasOperations()) {
			if (!$mode || $mode === 'begin') {
				$s .= $this->renderBegin();
			}
			if ((!$mode && $this->getValue('form errors')) || $mode === 'errors') {
				$s .= $this->renderErrors();
			}
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody();
		}
		if ((!$mode || $mode === 'end') && ($this->dataGrid->hasFilters() || $this->dataGrid->hasOperations())) {
			$s .= $this->renderEnd();
		}
		return $s;
	}
	
	
	/**
	 * Renders datagrid form begin.
	 * @return string
	 */
	public function renderBegin()
	{
		$form = $this->dataGrid->getForm(TRUE);

		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
		}

		return $form->getElementPrototype()->startTag();
	}
	
	
	/**
	 * Renders datagrid form end.
	 * @return string
	 */
	public function renderEnd()
	{
		$form = $this->dataGrid->getForm(TRUE);
		return $form->getElementPrototype()->endTag() . "\n";
	}


	/**
	 * Renders validation errors (probably not necessary).
	 * @return string
	 */
	public function renderErrors()
	{
		$form = $this->dataGrid->getForm(TRUE);
		
		$errors = $form->getErrors();
		if (count($errors)) {
			$ul = $this->getWrapper('error container');
			$li = $this->getWrapper('error item');

			foreach ($errors as $error) {
				$item = clone $li;
				if ($error instanceof Html) {
					$item->add($error);
				} else {
					$item->setText($error);
				}
				$ul->add($item);
			}
			return "\n" . $ul->render(0);
		}
	}
	
	
	/**
	 * Renders form body.
	 * @return string
	 */
	public function renderBody()
	{		
		$table = $this->getWrapper('grid container');
		
		// headers
		$table->add($this->generateHeaderRow());
		
		// filters
		if ($this->dataGrid->hasFilters()) {
			$table->add($this->generateFilterRow());
		}
		
		// rows
		$iterator = new SmartCachingIterator($this->dataGrid->getRows());
		foreach ($iterator as $data) {
			$row = $this->generateContentRow($data)->class($iterator->isEven() ? 'alt' : NULL);
			$table->add($row);
		}
		
		// footer
		$table->add($this->generateFooterRow());
		
		return $table->render(0);
	}
	
	
	/**
	 * Renders datagrid headrer.
	 * @return Html
	 */
	protected function generateHeaderRow()
	{
		$row = $this->getWrapper('row.header container');
		
		// checker
		if ($this->dataGrid->hasChecker()) {
			$cell = $this->getWrapper('row.header cell container')->class('checker');
			// TODO: remove and create by javascript
			// $cell->setHtml('<span class="icon icon-invert" style="display: none" title="Invertovat výběr" />');
			if ($this->dataGrid->hasFilters()) {
				$cell->rowspan(2);
			}
			$row->add($cell);
		}
		
		// headers
		foreach ($this->dataGrid->getColumns() as $column) {
			$value = $text = $column->caption;
			
			if ($column->isOrderable()) {
				$i = 1;
				parse_str($this->dataGrid->order, $list);
				foreach ($list as $field => $dir) {
					$list[$field] = array($dir, $i++);
				}
				
				$class = 'ajaxlink ';
				if (isset($list[$column->getName()])) {
					$class .= $list[$column->getName()][0] === 'a' ? 'asc' : 'desc';
				}
				
				if (count($list) > 1 && isset($list[$column->getName()])) {
					$text .= '&nbsp;<span>' . $list[$column->getName()][1] . '</span>';
				}
				
				
				$value = (string) Html::el('a')->href($column->getLink())->class($class)->setHtml($text);
			}
			
			$cell = $this->getWrapper('row.header cell container')->setHtml($value);
			$cell->attrs = $column->getHeaderPrototype()->attrs;			
			if ($column instanceof ActionColumn) $cell->class('actions');
			
			$row->add($cell);
		}
		
		return $row;
	}
	
	
	/**
	 * Renders datagrid filter.
	 * @return Html
	 */
	protected function generateFilterRow()
	{
		$row = $this->getWrapper('row.filter container');
		$form = $this->dataGrid->getForm(TRUE);
		
		foreach ($this->dataGrid->getColumns() as $column) {
			$cell = $this->getWrapper('row.filter cell container');
			
			if ($column instanceof ActionColumn) {
				$value = $form['filterSubmit']->getControl();
				$cell->class('actions');
				
			} else {
				if ($column->hasFilter()) {
					$value = $form['filters'][$column->getName()]->getControl();
				} else {
					$value = '&nbsp;';
				}
			}
			
			$cell->setHtml((string)$value);
			
			// TODO: Nastavovat i na filtrech?
			// $cell->attrs = $column->getCellPrototype()->attrs;
			$row->add($cell);
		}		
		return $row;
	}
	
	
	/**
	 * Renders datagrid row content.
	 * @param  DibiRow data
	 * @return Html
	 */
	protected function generateContentRow($data)
	{
		$form = $this->dataGrid->getForm(TRUE);
		$row = $this->getWrapper('row.content container');
		
		if ($this->dataGrid->hasChecker() || $this->dataGrid->hasActions()) {			
			$primary = $this->dataGrid->getKeyName();
			if (!array_key_exists($primary, $data)) {
				throw new InvalidArgumentException("Invalid name of key for group operations or actions. Column '" . $primary . "' does not exist in data source.");
			}
		}
		
		// checker
		if ($this->dataGrid->hasChecker()) {
			$value = $form['checker'][$data[$primary]]->getControl();
			$cell = $this->getWrapper('row.content cell container')->setHtml((string)$value)->class('checker');
			$row->add($cell);
		}
		
		// content
		foreach ($this->dataGrid->getColumns() as $column) {
			$cell = $this->getWrapper('row.content cell container');
			$cell->attrs = $column->getCellPrototype()->attrs;
			
			if ($column instanceof ActionColumn) {
				$value = '';
				foreach ($this->dataGrid->getActions() as $action) {
					$action->generateLink(array($primary => $data[$primary]));
					$value .= $action->getHtml();
				}
				$cell->class('actions');
				
			} else {
				$value = $column->formatContent($data[$column->getName()]);
			}
			
			$cell->setHtml((string)$value);
			$row->add($cell);
		}
		
		return $row;	
	}
	
	/**
	 * Renders datagrid footer.
	 * @return Html
	 */
	protected function generateFooterRow()
	{
		// TODO: implement!
		return Html::el('');
	}
	
	
	
	/**
	 * @param  string
	 * @return Html
	 */
	protected function getWrapper($name)
	{
		$data = $this->getValue($name);
		return $data instanceof Html ? clone $data : Html::el($data);
	}



	/**
	 * @param  string
	 * @return string
	 */
	protected function getValue($name)
	{
		$name = explode(' ', $name);
		if (count($name) == 3) {
			$data = & $this->wrappers[$name[0]][$name[1]][$name[2]];
		} else {
			$data = & $this->wrappers[$name[0]][$name[1]];
		}
		return $data;
	}
}