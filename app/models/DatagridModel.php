<?php

/**
 * Very basic and simple datagrid model example.
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
		return $this->connection->dataSource('SELECT * FROM [%n]', $table);
	}


	/**
	 * @return DibiDataSource
	 */
	public function getOrdersInfo()
	{
		return $this->connection->dataSource(
			'SELECT o.[orderNumber] AS [orderNumber], c.[customerNumber] AS [customerNumber],
				c.[customerName] AS [customerName], c.[addressLine1] AS [addressLine1],
				c.[city] AS [city], c.[country] AS [country], c.[creditLimit] AS [creditLimit],
				o.[orderDate] AS [orderDate], o.[status] AS [status], count(d.[productCode]) AS [productsCount]
			FROM [Orders] AS o
				JOIN [Customers] AS c ON c.[customerNumber] = o.[customerNumber]
				JOIN [OrderDetails] AS d ON d.[orderNumber] = o.[orderNumber]
			GROUP BY o.[orderNumber], c.[customerNumber], c.[customerName], c.[addressLine1],
				c.[city], c.[country], c.[creditLimit], o.[orderDate], o.[status]'
		);
	}


	/**
	 * @return DibiDataSource
	 */
	public function getOfficesInfo()
	{
		$driver = $this->getConnection()->getConfig('driver');
		return $driver == 'sqlite' || $driver == 'pdo' ? $this->getOfficesInfoSqlite() : $this->getOfficesInfoPgsql();
	}


	/**
	 * @return DibiDataSource
	 */
	private function getOfficesInfoPgsql()
	{
		return $this->connection->dataSource(
			'SELECT o.*,
				(SELECT COUNT(*) FROM [Employees] AS e WHERE o.[officeCode] = e.[officeCode] GROUP BY e.[officeCode]) AS [employeesCount],
				CASE WHEN (SELECT COUNT(*) FROM [Employees] AS e WHERE o.[officeCode] = e.[officeCode] GROUP BY e.[officeCode]) > 0 THEN TRUE ELSE FALSE END AS [hasEmployees]
				FROM [Offices] AS o'
		);
	}


	/**
	 * @return DibiDataSource
	 */
	private function getOfficesInfoSqlite()
	{
		return $this->connection->dataSource(
			'SELECT o.[officeCode] AS [officeCode], o.[city] AS [city], o.[phone] AS [phone],
				o.[addressLine1] AS [addressLine1], o.[addressLine2] AS [addressLine2], o.[state] AS [state],
				o.[country] AS [country], o.[postalCode] AS [postalCode], o.[position] AS [position],
				count(e.[employeeNumber]) AS [employeesCount], CASE WHEN count(e.[employeeNumber]) > 0 THEN 1 ELSE 0 END AS [hasEmployees]
				FROM [Offices] AS o
				JOIN [Employees] AS e ON o.[officeCode] = e.[officeCode]
				GROUP BY o.[officeCode]
				ORDER BY o.[position]'
		);
	}


	/**
	 * Position moving routine (only for table Offices).
	 * @param  string  which item of datagrid (position value)
	 * @param  string  move which direction
	 * @return void
	 */
	public function officePositionMove($key, $dir)
	{
		// TODO: write your own more sophisticated handler ;)
		$this->table = 'Offices';
		$this->primary = 'officeCode';

		$key = (int) $key;

		if ($dir == 'down') {
			$old = $this->findAll()->where('[position]=%i', $key)->fetch();
			$new = $this->findAll()->where('[position]=%i', $key + 1)->fetch();
			$this->update($old->officeCode, array('position' => $key + 1));
			$this->update($new->officeCode, array('position' => $key));

		} else {
			$old = $this->findAll()->where('[position]=%i', $key)->fetch();
			$new = $this->findAll()->where('[position]=%i', $key - 1)->fetch();
			$this->update($old->officeCode, array('position' => $key - 1));
			$this->update($new->officeCode, array('position' => $key));
		}
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
		return $this->findAll()->where("%n=%i", $this->primary, $id);
	}


	/**
	 * @return DibiFluent
	 */
	public function update($id, array $data)
	{
		return $this->connection->update($this->table, $data)->where("%n=%i", $this->primary, $id)->execute();
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
		return $this->connection->delete($this->table)->where("%n=%i", $this->primary, $id)->execute();
	}
}