<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 11:30:35
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-20 17:52:32
 */
namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\Resources\Resources;

class Attachment extends Resources {

	protected $attachment;

	public function createRawAttachment($attachment_name, $contentType, $filePath, $slug, $select = true)
	{
		$headers = array('Slug: '.$slug, 'Content-Type: ' . $contentType)
		$res = $this->azureDB->request->request(
			'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs/' . $this->azureDB->get('document')->getProperty('_rid') . '/attachments',
			'POST',
			file($filePath),
			'attachments',
			$this->azureDB->get('document')->getProperty('_rid'),
			$headers
			);
		
		if ($select == true)
		{
			$this->attachment = $res;
		}
		
		return $res;
	}

	public function createLinkAttachment($attachment_name, $contentType, $media, $select = true)
	{
		$res = $this->azureDB->request->request(
			'dbs/' . $this->azureDB->get('database')->getProperty('_rid') . '/colls/' . $this->azureDB->get('collection')->getProperty('_rid') . '/docs/' . $this->azureDB->get('document')->getProperty('_rid') . '/attachments',
			'POST',
			array('id' => $attachment_name, 'contentType' => $contentType, 'media' => $media),
			'attachments',
			$this->azureDB->get('document')->getProperty('_rid')
			);
		
		if ($select == true)
		{
			$this->attachment = $res;
		}
		
		return $res;
	}

}