<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 11:30:35
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-19 17:31:44
 */
namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\Resources\Resources;

class Document extends Resources {

	protected $document;

	public function _list()
	{
		$path = 'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs';

		$doc_list = $this->azureDB->request->request(
			$path,
			'GET',
			array(),
			'docs',
			$this->azureDB->get('collection')->getProperty('_rid')
			);

		return $doc_list;
	}

	// Select a document with his system id
	public function select($rid)
	{
		$path = 'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs/' . $rid;

		$doc = $this->azureDB->request->request(
			$path,
			'GET',
			array(),
			'docs',
			$rid
			);
	}

	/*
	* @param select : select document after creation
	*/
	public function create($json, $method = 'POST', $select = true)
	{
		$path = 'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs';
		$res = $this->azureDB->request->request(
			$path,
			$method,
			$json,
			'docs',
			$this->azureDB->get('collection')->getProperty('_rid')
			);
		
		if ($select == true)
		{
			$this->document = $res;
		}
		
		return $res;
	}

	public function replace($json, $select = true)
	{
		return $this->create($json, 'PUT', $select);
	}

	/*
	* if not document specified, delete current document
	*/
	public function delete($rid = null)
	{
		if ($rid)
		{
			$this->select($rid);
		}

		return $this->azureDB->request->request(
			'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs/' . $rid,
			'DELETE',
			array(),
			'docs',
			$rid
			);
	}

	// TODO : Parameters
	public function query($sql = '', $parameters = array())
	{
		$path = 'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs';

		$res = $this->azureDB->request->request(
			$path,
			'POST',
			array(
				'query' => $sql,
				'parameters' => $parameters
				),
			'docs',
			$this->azureDB->get('collection')->getProperty('_rid')
			);

		return $res;
	}
}