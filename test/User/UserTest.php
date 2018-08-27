<?php
# @Author: Cédric Rassaert <crassaert>
# @Date:   2018-01-08T10:21:08+01:00
# @Email:  crassaert@gmail.com
# @Last modified by:   crassaert
# @Last modified time: 2018-01-08T16:41:24+01:00

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Crassaert\AzureDocumentDB\AzureDocumentDB;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
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
        if (!isset($_ENV['AZURE_HOST']) && !isset($_ENV['AZURE_KEY'])) {
            require dirname(__FILE__) . '/../config.php';
        } else {
            define('AZURE_HOST', $_ENV['AZURE_HOST']);
            define('AZURE_KEY', $_ENV['AZURE_KEY']);
        }

        $db = new AzureDocumentDB(AZURE_HOST, AZURE_KEY, false);
        $db->get('database')->_list();

        $db->get('database')->delete('cosmos_test');
        $db->get('database')->create('cosmos_test');
        $db->get('database')->select('cosmos_test');

        $db->get('user')->_list();

        $db->get('database')->delete('cosmos_test');
    }
}
