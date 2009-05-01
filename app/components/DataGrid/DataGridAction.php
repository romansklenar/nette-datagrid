<?php


class DataGridAction extends Component implements IDataGridAction
{
	/**#@+ special action key */
	const ACTION_ADD	= 'add';
	const ACTION_EDIT	= 'edit';
	const ACTION_DETAIL = 'detail';
	const ACTION_DELETE = 'del';
	
	const WITH_KEY		= 'with';
	const WITHOUT_KEY	= 'without';
	/**#@-*/

	/** @var Html  control element template */
	protected $html;
	
	static public $ajaxClass = 'ajaxlink';

	/** @var string  one of special action key constants */
	public $type;

	/** @var string  textual link destination */
	public $destination;


	/**
	 * @param  string  textual title
	 * @param  string  textual link destination
	 * @param  string  one of special action key constants
	 *
	 * @note: if you want full datagrid ajax support, destination should not change module,
	 * 		  presenter or action and must be ended with exclamation mark (!)
	 */
	public function __construct($title, $destination, $icon = NULL, $useAjax = FALSE, $type = self::WITH_KEY)
	{
		parent::__construct();

		$this->type = $type;
		$this->destination = $destination;

		$a = Html::el('a')->title($title);
		if ($useAjax) $a->class(self::$ajaxClass);
		if ($icon !== NULL && $icon instanceof Html) {
			$icon->title($title);
			$a->add($icon);
		}
		$this->html = $a;
	}


	/**
	 * @return void 
	 */
	public function generateLink(array $args = NULL)
	{
		$presenter = $this->lookup('Nette\Application\Presenter', TRUE);
		switch ($this->type) {
			case self::WITHOUT_KEY: $link = $presenter->link($this->destination); break;
			case self::WITH_KEY: $link = $presenter->link($this->destination, $args); break;
			default: throw new InvalidArgumentException("Invalid type of action.");
		}

		$this->html->href($link);
	}


	public function getHtml()
	{
		return $this->html;
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


}

