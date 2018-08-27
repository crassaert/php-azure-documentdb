<?php
# @Author: Cédric Rassaert <crassaert>
# @Date:   2018-01-08T10:21:08+01:00
# @Email:  crassaert@gmail.com
# @Last modified by:   crassaert
# @Last modified time: 2018-01-08T16:22:37+01:00

namespace Crassaert\AzureDocumentDB\Request;

class AzureRequest
{
    protected $url;
    protected $key;
    protected $debug;

    public function __construct($url, $key, $debug = true)
    {
        $this->url = $url;
        $this->key = $key;
        $this->debug = $debug;
    }

    public function request($path, $method = 'GET', $options = array(), $resource_type = null, $resource_id = '', $additional_headers = array())
    {
        $curl = curl_init($this->url . '/' . $path);

        //curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if (!$resource_type) {
            $resource_type = current(explode('/', $path));
        }

        $this->setRequestData($curl, $method, $resource_type, $resource_id, $options, $additional_headers);

        ($this->debug) ?
        print "[[Debug: \nReq: ".curl_getinfo($curl, CURLINFO_HEADER_OUT) : $t=1;

        //curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $infos = curl_getinfo($curl);

        curl_close($curl);
        ($this->debug) ? print "\nRes: $result]]\n" : $t=1;

        $result = json_decode($result);
        $this->handleErrors($result, $infos);

        return $result;
    }

    public function multiRequest($path, $method = 'GET', $options = array(), $resource_type = null, $resource_id = '', $additional_headers = array())
    {
        $partitions = $options['parameters']['cross_partition_query'] ?? false;

        $multiCurl = curl_multi_init();
        $channels  = [];
        $results   = [];

        if (!$resource_type) {
            $resource_type = current(explode('/', $path));
        }

        foreach ($partitions as $partition) {

            $options['parameters']['partition_id'] = $partition;

            $curl = curl_init($this->url . '/' . $path);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_VERBOSE, $this->debug);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

            $this->setRequestData($curl, $method, $resource_type, $resource_id, $options, $additional_headers);

            curl_multi_add_handle($multiCurl, $curl);

            $channels[$partition] = $curl;
        }

        $active = null;

        do {
            $mrc = curl_multi_exec($multiCurl, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multiCurl) == -1) {
                continue;
            }

            do {
                $mrc = curl_multi_exec($multiCurl, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($channels as $channel) {
            $results[] = json_decode(curl_multi_getcontent($channel));
            curl_multi_remove_handle($multiCurl, $channel);
        }

        curl_multi_close($multiCurl);

        return $results;
    }

    protected function setRequestData(&$curl, $method, $resource_type, $resource_id, $options, $additional_headers)
    {
        $headers = array_merge($this->getAuthHeaders($method, $resource_type, $resource_id), $additional_headers);

        if ($method == 'POST' || $method == 'PUT') {
            $data = json_encode($options);

            if (isset($options['query'])) {
                $headers[] = 'x-ms-documentdb-isquery: True';
                $headers[] = 'x-ms-max-item-count: -1';
                $headers[] = 'Content-Type: application/sql';
                $headers[] = 'Content-Length: ' . strlen($options['query']);

                curl_setopt($curl, CURLOPT_POSTFIELDS, $options['query']);
            } else {
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'x-ms-max-item-count: -1';
                $headers[] = 'Content-Length: ' . strlen($data);

                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        $partitionId = $options['parameters']['partition_id'] ?? false;

        if ($partitionId !== false) {
            $headers[] = 'x-ms-documentdb-query-enablecrosspartition: True';
            $headers[] = 'x-ms-documentdb-partitionkeyrangeid: ' . $partitionId;
        }

        $headers[] = 'api-key:' . $this->key;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }


    /**
   * getAuthHeaders
   *
   * @link http://msdn.microsoft.com/en-us/library/azure/dn783368.aspx
   * @access private
   * @param string $verb          Request Method (GET, POST, PUT, DELETE)
   * @param string $resource_type Resource Type
   * @param string $resource_id   Resource ID
   * @return string Array of Request Headers
   */
    protected function getAuthHeaders($verb, $resource_type, $resource_id)
    {
        $x_ms_date = gmdate('D, d M Y H:i:s T', strtotime('+2 minutes'));
        $master = 'master';
        $token = '1.0';

        $key = base64_decode($this->key);
        $string_to_sign = $verb . "\n" .
        $resource_type . "\n" .
        $resource_id . "\n" .
        $x_ms_date . "\n" .
        "\n";

        $sig = base64_encode(hash_hmac('sha256', strtolower($string_to_sign), $key, true));

        return array(
            'Accept: application/json',
            'User-Agent: cosmosdb.php.sdk/1.0.0',
            'Cache-Control: no-cache',
            'x-ms-date: ' . $x_ms_date,
            'x-ms-version: 2017-02-22',
            'authorization: ' . urlencode("type=$master&ver=$token&sig=$sig")
            );
    }

    protected function handleErrors($response, $infos)
    {
        if ($infos['http_code'] >= 400) {
            throw new \Exception(
              $response->message . "\n" . print_r($infos) . "\n" . print_r($response),
              1);
        }
    }
}
