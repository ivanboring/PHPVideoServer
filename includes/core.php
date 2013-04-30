<?php

class core
{
	public static $query = null;
	public static $config = array();
	
	public function __construct($config)
	{
		self::$config = $config;
		
		self::$query = new Query($config['servers']['host'], $config['servers']['port']);
		
		$get_structure = self::$query->call($config['movie_index'] . '/_settings', 'GET');
		
		if (!isset($get_structure[$config['movie_index']])) {
			$install = new Install();
			$install->install();
		}
	}
	
	protected function sanitize($string, $force_lowercase = true, $anal = false) {
	    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
	                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
	                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
	    $clean = trim(str_replace($strip, "", strip_tags($string)));
	    $clean = preg_replace('/\s+/', "-", $clean);
	    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
	    return ($force_lowercase) ?
	        (function_exists('mb_strtolower')) ?
	            mb_strtolower($clean, 'UTF-8') :
	            strtolower($clean) :
	        $clean;
	}
}
?>