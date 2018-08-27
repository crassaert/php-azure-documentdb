<?php
# @Author: Cédric Rassaert <crassaert>
# @Date:   2018-01-08T10:21:08+01:00
# @Email:  crassaert@gmail.com
# @Last modified by:   crassaert
# @Last modified time: 2018-01-08T14:04:05+01:00

namespace Crassaert\AzureDocumentDB\Resources;

use Crassaert\AzureDocumentDB\Resources\Resources;

class Document extends Resources
{
    protected $document;

    public function _list()
    {
        $path = 'dbs/' .
                $this->azureDB->get('database')->getProperty('_rid') .
                '/colls/' .
                $this->azureDB->get('collection')->getProperty('_rid') .
                '/docs';

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
        $path = 'dbs/' .
                $this->azureDB->get('database')->getProperty('_rid') .
                '/colls/' .
                $this->azureDB->get('collection')->getProperty('_rid') .
                '/docs/' . $rid;

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
        $path = 'dbs/' .
                $this->azureDB->get('database')->getProperty('_rid') .
                '/colls/' .
                $this->azureDB->get('collection')->getProperty('_rid') .
                '/docs';

        $res = $this->azureDB->request->request(
            $path,
            $method,
            $json,
            'docs',
            $this->azureDB->get('collection')->getProperty('_rid')
            );

        if ($select == true) {
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
        if ($rid) {
            $this->select($rid);
        }

        return $this->azureDB->request->request(
            'dbs/' .
            $this->azureDB->get('database')->getProperty('_rid') .
            '/colls/' .
            $this->azureDB->get('collection')->getProperty('_rid') .
            '/docs/' .
            $rid,
            'DELETE',
            array(),
            'docs',
            $rid
            );
    }

    // TODO : Parameters
    public function query($sql = '', $parameters = array())
    {
        $cpQuery = $parameters['cross_partition_query'] ?? false;
        $method  = "request";

        if ($cpQuery === true) {
            $cpQuery = [];

            foreach ($this->getPartitions()->PartitionKeyRanges as $partition) {
                $cpQuery[] = $partition->id;
            }

            $parameters['cross_partition_query'] = $cpQuery;
            $method = "multiRequest";
        }

        $path = 'dbs/' .
                $this->azureDB->get('database')->getProperty('_rid') .
                '/colls/' .
                $this->azureDB->get('collection')->getProperty('_rid') .
                '/docs';

        $res = $this->azureDB->request->$method(
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

    public function getPartitions()
    {
        $path = 'dbs/' .
                $this->azureDB->get('database')->getProperty('_rid') .
                '/colls/' .
                $this->azureDB->get('collection')->getProperty('_rid') .
                '/pkranges';

        $res = $this->azureDB->request->request(
            $path,
            'GET',
            array(),
            'pkranges',
            $this->azureDB->get('collection')->getProperty('_rid')
        );

        return $res;
    }
}
