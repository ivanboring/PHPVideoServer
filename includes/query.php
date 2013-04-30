<?php

/**
 * Query takes care of all calls to Elasticsearch
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class Query
{
    /**
     * Construnction
	 * 
     * @param string $server
     * @param string $port
     */
    public function __construct($server = 'http://localhost', $port = '9200')
    {
        $this->es_server = trim($server, '/') . ':' . $port;
    }

    /**
     * Makes a call
	 * 
     * @param string $path The path to call
     * @param string $method The method to use
     * @param string $data The data to pass
	 * @param string $header The content type header to pass
	 * @param string $parameters The query string parameters header to pass
	 * 
     * @return array
     */
    public function call($path, $method = 'POST', $data = '', $header = '', $parameters = array())
    {
        $ch = curl_init();

        $url = $this->es_server . '/' . ltrim($path, '/');

		
		if (count($parameters))
		{
			$url .= '?' . http_build_query($parameters);
		}
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            // Trim the data
            //$data = $this->trimData($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
		
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: ' . $header));
		}
		
        $data = curl_exec($ch);

        curl_close($ch);

        $jsondata = json_decode($data);
		
        // If it is JSON, convert it to array and return
        if ($jsondata) {
            $jsondata = $this->objectToArray($jsondata);

            return $jsondata;
        }

        return $data;
    }

    private function trimData($json)
    {
        return str_replace(array("\t","\n", "\r\n", "\r"), "", $json);
    }

    private function objectToArray($object)
    {
        $array = is_object($object) ? get_object_vars($object) : $object;
        foreach ($array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
            $array[$key] = $value;
        }

        return $array;
    }
}
?>