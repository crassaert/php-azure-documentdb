<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 11:30:35
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-19 16:27:35
 */
namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\Resources\Resources;

class Collection extends Resources {

	protected $collection;

	public function _list()
	{
		$path = 'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls';

		$col_list = $this->azureDB->request->request(
			$path,
			'GET',
			array(),
			'colls',
			$this->azureDB->get('database')->getProperty('_rid')
			);

		return $col_list->DocumentCollections;
	}

	public function select($col_name)
	{
		// Fetching rid_db
		foreach ($this->_list() as $collection)
		{
			if ($collection->id == $col_name)
			{
				$this->collection = $collection;

				return $collection;
			}
		}
	}

	/*
	* @param select : select collection after creation
	*/
	public function create($col_name, $select = true)
	{
		$res = $this->azureDB->request->request(
			'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls',
			'POST',
			array('id' => $col_name),
			'colls',
			$this->azureDB->get('database')->getProperty('_rid')
			);
		
		if ($select == true)
		{
			$this->collection = $res;
		}
		
		return $res;
	}

	/*
	* if not collection name specified, delete current collection
	*/
	public function delete($col_name = null)
	{
		if ($col_name)
		{
			$this->select($col_name);
		}

		return $this->azureDB->request->request(
			'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->collection->_rid,
			'DELETE',
			array(),
			'colls',
			$this->collection->_rid
			);
	}

	public function getProperty($property)
	{
		return $this->collection->$property;
	}
}