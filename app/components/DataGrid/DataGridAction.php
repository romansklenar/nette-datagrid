<?php

require_once dirname(__FILE__) . '/IDataGridAction.php';



/**
 * Representation of data grid action.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class DataGridAction extends Component implements IDataGridAction
{
	/**#@+ special action key */
	const WITH_KEY		= TRUE;
	const WITHOUT_KEY	= FALSE;
	/**#@-*/

	/** @var Html  action element template */
	protected $html;

	/** @var string */
	static public $ajaxClass = 'datagrid-ajax';

	/** @var string */
	public $destination;

	/** @var bool|string */
	public $key;


	/**
	 * Data grid action constructor.
	 * @note   for full ajax support, destination should not change module,
	 * @note   presenter or action and must be ended with exclamation mark (!)
	 *
	 * @param  string  textual title
	 * @param  string  textual link destination
	 * @param  Html    element which is added to a generated link
	 * @param  bool    use ajax? (add class self::$ajaxClass into generated link)
	 * @param  mixed   generate link with argument? (if yes you can specify name of parameter
	 * 				   otherwise variable DataGrid::$keyName will be used and must be defined)
	 * @return void
	 */
	public function __construct($title, $destination, Html $icon = NULL, $useAjax = FALSE, $key = self::WITH_KEY)
	{
		parent::__construct();
		$this->destination = $destination;
		$this->key = $key;

		$a = Html::el('a')->title($title);
		if ($useAjax) $a->addClass(self::$ajaxClass);

		if ($icon !== NULL && $icon instanceof Html) {
			$a->add($icon);
		} else {
			$a->setText($title);
		}
		$this->html = $a;
	}


	/**
	 * Generates action's link. (use before data grid is going to be rendered)
	 * @return void
	 */
	public function generateLink(array $args = NULL)
	{
		$dataGrid = $this->lookup('DataGrid', TRUE);
		$control = $dataGrid->lookup('Nette\Application\Control', TRUE);

		switch ($this->key) {
		case self::WITHOUT_KEY:
			$link = $control->link($this->destination); break;
		case self::WITH_KEY:
		default:
			$key = $this->key == NULL || is_bool($this->key) ? $dataGrid->keyName : $this->key;
			$link = $control->link($this->destination, array($key => $args[$dataGrid->keyName])); break;
		}

		$this->html->href($link);
	}



	/********************* interface \IDataGridAction *********************/



	/**
	 * Gets action element template.
	 * @return Html
	 */
	public function getHtml()
	{
		return $this->html;
	}

}