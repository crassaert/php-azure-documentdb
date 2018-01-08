<?php
# @Author: Cédric Rassaert <crassaert>
# @Date:   2018-01-08T10:21:08+01:00
# @Email:  crassaert@gmail.com
# @Last modified by:   crassaert
# @Last modified time: 2018-01-08T16:12:32+01:00

require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/../vendor/autoload.php';

use Crassaert\AzureDocumentDB\AzureDocumentDB;
use PHPUnit\Framework\TestCase;

class OperationsTest extends TestCase
{
	const MAX_USERS = 10;

    protected function getFakeUser()
    {
        $faker = Faker\Factory::create();

        $usr = [
						'id'			=> $faker->md5,
            'name'		=> $faker->name,
            'address' => $faker->address,
            'email'		=> $faker->email
        ];

        return $usr;
    }

    public function testDatabase()
    {
        $db = new AzureDocumentDB(AZURE_HOST, AZURE_KEY, false);
        $db->get('database')->_list();
        $db->get('database')->create('cosmos_test');
        $db->get('database')->select('cosmos_test');
        $db->get('collection')->create('user');

        // Creating 10 fake users
        for ($i = 1; $i <= self::MAX_USERS; $i++) {
            $db->get('document')->create($this->getFakeUser());
        }

        $db->get('collection')->_list();
        $users = $db->get('document')->_list();

				$this->assertEquals(self::MAX_USERS, count($users->Documents));

        $db->get('document')->query('select * from user');
        $db->get('collection')->delete('user');

        $db->get('database')->delete('cosmos_test');
    }
}
