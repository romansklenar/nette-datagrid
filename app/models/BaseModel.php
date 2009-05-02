<?php

/**
 * Base abstract model class.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
abstract class BaseModel extends Object implements IModel
{
	/** @var DibiConnection */
	protected $connection;	
	
	/** @var string  table name */
	protected $table;
	
	/** @var string|array  primary key column name */
	protected $primary = 'id';
	
	/** @var array of function(IModel $sender) */
	public $onStartup;
	
	/** @var array of function(IModel $sender) */
	public $onShutdown;


	public function __construct($table = NULL, $primary = NULL)
	{
		$this->onStartup($this);
		$this->connection = dibi::getConnection();
		
		if ($table) $this->setTable($table);
		if ($primary) $this->setPrimary($primary);
	}

	
	public function __destruct()
	{
		$this->onShutdown($this);
		$this->connection->disconnect();
	}
	
	public static function initialize()
	{
		dibi::connect(Environment::getConfig('database'));

		if (Environment::getConfig('database')->profiler) {
			dibi::getProfiler()->setFile(Environment::expand('%logDir%') . '/sql.log');
		}
	}
	
	

	/***** Public getters and setters *****/
	
	
	
	public function getTable()
	{
		return $this->table;
	}	
	

	public function setTable($table)
	{
		$this->table = $table;
	}	
	

	public function getPrimary()
	{
		return $this->primary;
	}	
	

	public function setPrimary($primary)
	{
		$this->primary = $primary;
	}
		
	
	
	/***** Model's API *****/
	
	
	
	public function findAll()
	{
		throw new NotImplementedExceptiont('Method is not implemented.');
	}
	

	public function find($id)
	{
		throw new NotImplementedExceptiont('Method is not implemented.');
	}
	

	public function update($id, array $data)
	{
		throw new NotImplementedExceptiont('Method update is not implemented.');
	}
	

	public function insert(array $data)
	{
		throw new NotImplementedExceptiont('Method insert is not implemented.');
	}
	

	public function delete($id)
	{
		throw new NotImplementedExceptiont('Method delete is not implemented.');
	}
	
}