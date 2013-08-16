<?php

class movie extends core
{
	function __construct()
	{
		
	}
	
	public function stream($id)
	{
		$response = self::$query->call(self::$config['movie_index'] . '/movie/' . $id, 'GET');
		if (isset($response['_source'])) {
	    	set_time_limit(0);

			$filepath = $response['_source']['filepath'];
			
			$fileinfo = finfo_open(FILEINFO_MIME_TYPE);
			$contenttype = finfo_file($fileinfo, $filepath);
			finfo_close($fileinfo);
			
			header('Content-Disposition: inline; filename="'.basename($filepath).'"');
			header('Content-Type: ' . $contenttype);
			
			$fsize = filesize($filepath);
			header('Content-Length: ' . $fsize);
			session_cache_limiter('nocache');
			header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Pragma: no-cache');
			$file = fopen($filepath, 'rb');
			while(!feof($file)) {
				echo fread($file, 1024*64);
				if (connection_status()!=0) {
			          @fclose($file);
			          die();
		        }
			}
			
			@fclose($file);
			exit;
		} 	
	}
}
