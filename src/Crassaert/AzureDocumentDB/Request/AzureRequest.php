<?php
/**
 * @Author: cedric
 * @Date:   2015-11-17 11:27:42
 * @Last Modified by:   cedric
 * @Last Modified time: 2016-01-14 11:45:58
 */

namespace Crassaert\AzureDocumentDB\Request;

class AzureRequest {

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

		//curl_setopt($curl, CURLOPT_HEADER, $this->debug);
		curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, $this->debug);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

		if (!$resource_type)
		{
			$resource_type = current(explode('/', $path));
		}

		$this->setRequestData($curl, $method, $resource_type, $resource_id, $options, $additional_headers);

		($this->debug) ?
		print "[[Debug: \nReq: ".curl_getinfo($curl, CURLINFO_HEADER_OUT) : $t=1;

		//curl_setopt_array($curl, $options);
		$result = curl_exec($curl);
		curl_close($curl);
		($this->debug) ? print "\nRes: $result]]\n" : $t=1;

		return json_decode($result);
	}

	protected function setRequestData(&$curl, $method, $resource_type, $resource_id, $options, $additional_headers)
	{
		$headers = array_merge($this->getAuthHeaders($method, $resource_type, $resource_id), $additional_headers);

		if ($method == 'POST' || $method == 'PUT')
		{                                        
			$data = json_encode($options);

			if (isset($options['query']))
			{
				$headers[] = 'x-ms-documentdb-isquery: True';
				$headers[] = 'Content-Type: application/sql';
				$headers[] = 'Content-Length: ' . strlen($options['query']);

				curl_setopt($curl, CURLOPT_POSTFIELDS, $options['query']);
			}
			else
			{
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Content-Length: ' . strlen($data);

				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			}
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

		return Array(
			'Accept: application/json',
			'User-Agent: documentdb.php.sdk/1.0.0',
			'Cache-Control: no-cache',
			'x-ms-date: ' . $x_ms_date,
			'x-ms-version: 2015-04-08',
			'authorization: ' . urlencode("type=$master&ver=$token&sig=$sig")
			);
	}
}