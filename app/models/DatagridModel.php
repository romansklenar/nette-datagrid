<?php



class DatagridModel extends BaseModel
{

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
		
		if (Environment::getConfig()->database->profiler) {
			$this->connection->getProfiler()->setFile(Environment::expand('%logDir%') . '/sql.log');
		}
	}
	
	
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

	public function getCustomerAndOrderInfo() {
		return $this->connection->dataSource(
			'SELECT c.*, count(o.orderNumber) AS orders, o.orderDate, o.status
			FROM Customers AS c
				LEFT JOIN Orders AS o ON c.customerNumber = o.customerNumber 
			GROUP BY c.customerNumber'
		);
	}
	
	
	public function findAll()
	{
		return $this->connection->select('*')->from($this->table);
	}

	public function find($id)
	{
		return $this->findAll()->where("$this->primary=%i", $id);
	}

	public function update($id, array $data)
	{
		return $this->connection->update($this->table, $data)->where("$this->primary=%i", $id)->execute();
	}

	public function insert(array $data)
	{
		return $this->connection->insert($this->table, $data)->execute(dibi::IDENTIFIER);
	}

	public function delete($id)
	{
		return $this->connection->delete($this->table)->where("$this->primary=%i", $id)->execute();
	}

}