<?php

namespace DataGrid\Columns;
use Nette, DataGrid;

/**
 * Representation of data grid action column.
 * If you want to write your own implementation you must inherit this class.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class ActionColumn extends Column implements \ArrayAccess
{
	private $actions = array();

	/**
	 * Action column constructor.
	 * @param  string  column's textual caption
	 * @return void
	 */
	//public function __construct($caption = 'Actions')
	public function __construct(DataGrid\DataGrid $dataGrid, $name, $caption = 'Actions')
	{
		parent::__construct($dataGrid, $name, $caption);
		//$this->addComponent(new Nette\ComponentContainer, 'actions');
		//$this->removeComponent($this->getComponent('filters'));
		$this->orderable = FALSE;
	}


	/**
	 * Has column filter box?
	 * @return bool
	 */
	public function hasFilter()
	{
		return FALSE;
	}


	/**
	 * Returns column's filter.
	 * @param  bool   throw exception if component doesn't exist?
	 * @return DataGrid\Filters\IColumnFilter|NULL
	 * @throws \InvalidStateException
	 */
	public function getFilter($need = TRUE)
	{
		if ($need == TRUE) {
			throw new \InvalidStateException("DataGrid\Columns\ActionColumn cannot has filter.");
		}
		return NULL;
	}


	/**
	 * Action factory.
	 * @param  string  textual title
	 * @param  string  textual link destination
	 * @param  Html    element which is added to a generated link
	 * @param  bool    use ajax? (add class self::$ajaxClass into generated link)
	 * @param  bool    generate link with argument? (variable $keyName must be defined in data grid)
	 * @return DataGrid\Action
	 */
	public function addAction($title, $signal, $icon = NULL, $useAjax = FALSE, $type = DataGrid\Action::WITH_KEY)
	{
		$action = new DataGrid\Action($this->dataGrid, $title, $signal, $icon, $useAjax, $type);
		$this[] = $action;
		return $action;
	}


	/**
	 * Does column has any action?
	 * @return bool
	 */
	public function hasAction($type = 'DataGrid\IAction')
	{
		return count($this->getActions($type)) > 0;
	}


	/**
	 * Returns column's action specified by name.
	 * @param  string action's name
	 * @param  bool   throw exception if component doesn't exist?
	 * @return DataGrid\IAction|NULL
	 * @todo return type
	 */
	public function getAction($name = NULL, $need = TRUE)
	{
		//return $this->getComponent('actions')->getComponent($name, $need);
		if (array_key_exists($name, $this->actions)) {
			return $this->actions[$name];
		} else {
			if ($need) {
				throw new \InvalidStateException("Action $name not found");
			}
			return NULL;
		}
	}


	/**
	 * Iterates over all column's actions.
	 * @param  string
	 * @return \ArrayIterator|NULL
	 */
	public function getActions($type = 'DataGrid\IAction')
	{
		$actions = new \ArrayObject();
		
		/*foreach ($this->getComponent('actions')->getComponents(FALSE, $type) as $action) {
			$actions->append($action);
		}*/

		foreach (new Nette\InstanceFilterIterator(new \ArrayIterator($this->actions), $type) as $action) {
			$actions->append($action);
		}
		
		return $actions->getIterator();
	}


	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @param  \DibiRow|array
	 * @return string
	 * @throws \InvalidStateException
	 */
	public function formatContent($value, $data = NULL)
	{
		throw new \InvalidStateException("DataGrid\Columns\ActionColumn cannot be formated.");
	}


	/**
	 * Filters data source.
	 * @param  mixed
	 * @throws \InvalidStateException
	 * @return void
	 */
	public function applyFilter($value)
	{
		throw new \InvalidStateException("DataGrid\Columns\ActionColumn cannot be filtered.");
	}



	/********************* interface \ArrayAccess *********************/



	/**
	 * Adds the action to the container.
	 * @param  string  action name
	 * @param  DataGrid\IAction
	 * @return void.
	 */
	final public function offsetSet($name, $action)
	{
		if (!$action instanceof DataGrid\IAction) {
			throw new \InvalidArgumentException("DataGrid\Columns\ActionColumn accepts only DataGrid\IAction objects.");
		}
		
		//$this->getComponent('actions')->addComponent($component, $name == NULL ? count($this->getActions()) : $name);
		$this->actions[$name == NULL ? count($this->getActions()) : $name] = $action;
	}


	/**
	 * Returns action specified by name. Throws exception if action doesn't exist.
	 * @param  string  action name
	 * @return DataGrid\IAction
	 * @throws \InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getAction((string) $name, TRUE);
	}


	/**
	 * Does action specified by name exists?
	 * @param  string  action name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getAction($name, FALSE) !== NULL;
	}


	/**
	 * Removes action from the container. Throws exception if action doesn't exist.
	 * @param  string  action name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		/*$component = $this->getAction($name, FALSE);
		if ($component !== NULL) {
			$this->getComponent('actions')->removeComponent($component);
		}*/
		unset($this->actions[$name]);
	}
}