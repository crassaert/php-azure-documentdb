<?php
/**
 * @Author: cedric
 * @Date:   2018-08-27 17:20:43
 * @Last Modified by:   cedric
 * @Last Modified time: 2018-08-27 17:20:43
 */

namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\Resources\Resources;

class User extends Resources
{
    public function _list()
    {
        $path = 'dbs/' .
              $this->azureDB->get('database')->getProperty('_rid') .
              '/users';

        $user_list = $this->azureDB->request->request(
              $path,
              'GET'
              );

        return $user_list;
    }
}
