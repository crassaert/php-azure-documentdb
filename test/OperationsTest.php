<?php
/**
 * @Author: cedric
 * @Date:   2015-11-19 12:02:06
 * @Last Modified by:   cedric
 * @Last Modified time: 2015-11-20 17:37:17
 */

require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/../vendor/autoload.php';

use Crassaert\AzureDocumentDB\AzureDocumentDB;

class OperationsTest extends PHPUnit_Framework_TestCase
{

	public function testDatabase()
	{
		$db = new AzureDocumentDB(AZURE_HOST, AZURE_KEY, false);
		$db->get('database')->_list();
		$db->get('database')->create('wamiz_test');
		$db->get('database')->delete('wamiz_test');
		$db->get('database')->select('wamiz');
		$db->get('collection')->create('user_test');
		$db->get('collection')->_list();
		$db->get('collection')->delete('user_test');
		$db->get('collection')->select('user_actions');
		var_dump($db->get('document')->query('select * from user_actions u where u.id > "723"'));
	}
}