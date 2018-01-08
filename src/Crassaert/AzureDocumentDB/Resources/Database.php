<?php
# @Author: Cédric Rassaert <crassaert>
# @Date:   2018-01-08T10:21:08+01:00
# @Email:  crassaert@gmail.com
# @Last modified by:   crassaert
# @Last modified time: 2018-01-08T14:49:13+01:00

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

		throw new \Exception("The database " . $db_name . " does not exists", 1);

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
