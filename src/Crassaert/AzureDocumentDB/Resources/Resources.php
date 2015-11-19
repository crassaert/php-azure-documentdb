<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 16:24:26
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-19 16:25:25
 */

namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\AzureDocumentDB;

class Resources
{
	public function __construct(AzureDocumentDB $azureDB)
	{
		$this->azureDB = $azureDB;
	}
}