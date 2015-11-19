<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 11:30:35
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-19 16:26:07
 */
namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\Resources\Resources;

class Database extends Resources {

	protected $azureDB;

	public function _list()
	{
		$db_list = $this->azureDB->request->request('dbs');

		return $db_list->Databases;
	}

	public function select($db_name)
	{
		// Fetching rid_db
		foreach ($this->_list() as $database)
		{
			if ($database->id == $db_name)
			{
				$this->database = $database;

				return $database;
			}
		}
	}

	/*
	* @param select : select database after creation
	*/
	public function create($db_name, $select = true)
	{
		$res = $this->azureDB->request->request('dbs', 'POST', array('id' => $db_name));
		
		if ($select == true)
		{
			$this->database = $res;
		}
		
		return $res;
	}

	/*
	* if not database name specified, delete current database
	*/
	public function delete($db_name = null)
	{
		if ($db_name)
		{
			$this->select($db_name);
		}

		return $this->azureDB->request->request('dbs/' . $this->database->_rid, 'DELETE', array(), null, $this->database->_rid);
	}

	public function getProperty($property)
	{
		return $this->database->$property;
	}
}