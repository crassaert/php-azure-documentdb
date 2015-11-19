<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 11:12:25
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-19 15:47:04
 */
namespace Crassaert\AzureDocumentDB;

use Crassaert\AzureDocumentDB\Request\AzureRequest;

class AzureDocumentDB {

	public $request;

	protected $resourceContainer;

	public function __construct($url, $key, $debug = true)
	{
		$this->request = new AzureRequest($url, $key, $debug);
	}

	public function get($resource_type)
	{
		if (!isset($this->resourceContainer[$resource_type]))
		{
			$class = 'Crassaert\\AzureDocumentDB\\Resources\\' . ucfirst($resource_type);
			$this->resourceContainer[$resource_type] = new $class($this);
		}

		return $this->resourceContainer[$resource_type];
	}
}