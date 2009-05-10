<?php

/**
 * Data grid model example.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class DatagridModel extends BaseModel
{
    /**
     * Data grid model constructor.
     * @param  string  table name
     * @param  string  primary key name
     * @return void
     */
	public function __construct($table = NULL, $primary = NULL)
	{
		parent::__construct($table, $primary);
		
		if (!isset($this->primary) && isset($this->table)) {
			try {
				$dbInfo = $this->connection->getDatabaseInfo();
				$this->primary = $dbInfo->getTable($this->table)->getPrimaryKey()->getName();
			} catch(Exception $e) {
				Debug::processException($e);
				throw new InvalidArgumentException("Model must have one primary key.");
			}
		}

		if ($this->connection->profiler) {
			$this->connection->getProfiler()->setFile(Environment::expand('%logDir%') . '/sql.log');
		}
	}
	
	
    /**
     * @return DibiConnection
     */
	public function getConnection()
	{
		return $this->connection;
	}


    /**
     * @return array
     */
	public function getTableNames()
	{
		return $this->connection->getDatabaseInfo()->getTableNames();
	}



    /**
     * @return DibiDataSource
     */
	public function getDataSource($table)
	{
		return $this->connection->dataSource('SELECT * FROM %n', $table);
	}

	
	/**
	 * @return DibiDataSource
	 */
	public function getCustomerAndOrderInfo()
	{
		return $this->connection->dataSource(
			'SELECT c.*, count(o.orderNumber) AS orders, o.orderDate, o.status
			FROM customers AS c
				LEFT JOIN orders AS o ON c.customerNumber = o.customerNumber 
			GROUP BY c.customerNumber'
		);
	}
	
	
	/**
	 * @return DibiFluent
	 */
	public function findAll()
	{
		return $this->connection->select('*')->from($this->table);
	}

	
	/**
	 * @return DibiFluent
	 */
	public function find($id)
	{
		return $this->findAll()->where("$this->primary=%i", $id);
	}

	
	/**
	 * @return DibiFluent
	 */
	public function update($id, array $data)
	{
		return $this->connection->update($this->table, $data)->where("$this->primary=%i", $id)->execute();
	}

	
	/**
	 * @return DibiFluent
	 */
	public function insert(array $data)
	{
		return $this->connection->insert($this->table, $data)->execute(dibi::IDENTIFIER);
	}

	
	/**
	 * @return DibiFluent
	 */
	public function delete($id)
	{
		return $this->connection->delete($this->table)->where("$this->primary=%i", $id)->execute();
	}
}