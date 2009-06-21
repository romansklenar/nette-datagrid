<?php

/**
 * Common model inteface.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
interface IModel
{
	/**
	 * Setups database connection
	 * @return void
	 */
	public static function initialize();



	/***** Public getters and setters *****/



	/**
	 * Gets table name
	 * @return string
	 */
	public function getTable();


	/**
	 * Sets table name
	 * @param $table  table name
	 * @return void
	 */
	public function setTable($table);


	/**
	 * Gets primary key(s)
	 * @return string|array
	 */
	public function getPrimary();


	/**
	 * Sets primary key(s)
	 * @param $primary
	 * @return void
	 */
	public function setPrimary($primary);



	/***** Model's API *****/



	/**
	 * Common render method.
	 * @return DibiDataSource
	 */
	public function findAll();


	/**
	 * Find occurrences matching the primary key.
	 * @param $id
	 * @return DibiDataSource
	 */
	public function find($id);


	/**
	 * Updates database row.
	 * @param $id
	 * @param $data
	 * @return DibiFluent
	 */
	public function update($id, array $data);


	/**
	 * Inserts data into table.
	 * @param $data
	 * @return DibiFluent
	 */
	public function insert(array $data);


	/**
	 * Deletes row(s) matching primary key.
	 * @param $id
	 * @return DibiFluent
	 */
	public function delete($id);
}