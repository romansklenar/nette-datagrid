<?php

require_once LIBS_DIR . '/Nette/Component.php';

require_once dirname(__FILE__) . '/IDataGridAction.php';



/**
 * Representation of data grid action.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id$
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
	static public $ajaxClass = 'ajax';

	/** @var string */
	public $type;

	/** @var string */
	public $destination;


	/**
	 * Data grid action constructor.
	 * @note   for full ajax support, destination should not change module, 
	 * @note   presenter or action and must be ended with exclamation mark (!)
	 * 
	 * @param  string  textual title
	 * @param  string  textual link destination
	 * @param  Html    element which is added to a generated link
	 * @param  bool    use ajax? (add class self::$ajaxClass into generated link)
	 * @param  bool    generate link with argument? (variable $keyName must be defined in data grid)
	 * @return void
	 */
	public function __construct($title, $destination, Html $icon = NULL, $useAjax = FALSE, $type = self::WITH_KEY)
	{
		parent::__construct();
		$this->type = $type;
		$this->destination = $destination;
		
		$a = Html::el('a')->title($title);
		if ($useAjax) $a->class[] = self::$ajaxClass;
		
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
		$presenter = $this->lookup('Nette\Application\Presenter', TRUE);
		switch ($this->type) {
			case self::WITHOUT_KEY: $link = $presenter->link($this->destination); break;
			case self::WITH_KEY: $link = $presenter->link($this->destination, $args); break;
			default: throw new InvalidArgumentException("Invalid type of action.");
		}
		
		$this->html->href($link);
	}


	/**
	 * Returns data grid.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return Form
	 */
	public function getDataGrid($need = TRUE)
	{
		return $this->lookup('DataGrid', $need);
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