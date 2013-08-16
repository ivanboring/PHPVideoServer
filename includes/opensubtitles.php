<?php

class opensubtitles extends core
{
	public static $xmlQuery = null;
	public $token = '';
	
    function __construct() {
        self::$xmlQuery = new Query(self::$config['opensubtitles']['host'], self::$config['opensubtitles']['port']);
    }
	
	public function rpcCall($method, $params = null) {
        $post = xmlrpc_encode_request($method, $params, array('encoding' => ''));
        return self::$xmlQuery->call(self::$config['opensubtitles']['method'], 'POST', $post, 'text/xml');
    }
	
	public function findFile($file, $name)
	{
		$hash = $this->OpenSubtitlesHash($file);
		$id =  $this->OpenSubtitlesSearch($hash, $this->filesize64($file), basename($file));
		if(!$id) $id = $this->OpenSubtitlesSearchTitle($name);

		return $id;
	}
	
	public function OpenSubtitlesLogin() 
	{
		$data = xmlrpc_decode($this->rpcCall('LogIn', array('', '', '', 'OS Test User Agent')));
		$this->token = $data['token'];
	}

	public function OpenSubtitlesSearchTitle($name)
	{
		if (!$this->token) $this->OpenSubtitlesLogin();
		
		$xml = $this->rpcCall('SearchMoviesOnIMDB', array($this->token, $name));
		
		$data = xmlrpc_decode($xml);

		if(!isset($data['data'][0]['id'])) return null;

		return $data['data'][0]['id'];		
	}
	
	public function OpenSubtitlesSearch($hash, $size, $name)
	{
		if (!$this->token) $this->OpenSubtitlesLogin();
		
		$name = substr($name, 0, -4);

		$xml = $this->rpcCall('SearchSubtitles', array($this->token, array(array('moviehash' => $hash, 'moviebytesize' => $size))));
		
		$data = xmlrpc_decode($xml);
		
		if(!isset($data['data'][0])) return null;
		
		foreach($data['data'] as $value)
		{
			if($value['MovieReleaseName'] == $name) {
				return $value['IDMovieImdb'];
			}
		}
		
		return $data['data'][0]['IDMovieImdb'];		
	}
	
	public function OpenSubtitlesHash($file) 
	{
	    $handle = fopen($file, "rb");
	    $fsize = filesize($file);
	    
	    $hash = array(3 => 0, 
	                  2 => 0, 
	                  1 => ($fsize >> 16) & 0xFFFF, 
	                  0 => $fsize & 0xFFFF);
	        
	    for ($i = 0; $i < 8192; $i++)
	    {
	        $tmp = $this->ReadUINT64($handle);
	        $hash = $this->AddUINT64($hash, $tmp);
	    }
	    
	    $offset = $fsize - 65536;
	    fseek($handle, $offset > 0 ? $offset : 0, SEEK_SET);
	    
	    for ($i = 0; $i < 8192; $i++)
	    {
	        $tmp = $this->ReadUINT64($handle);
	        $hash = $this->AddUINT64($hash, $tmp);         
	    }
	    
	    fclose($handle);
	        return $this->UINT64FormatHex($hash);
	}
	
	private function ReadUINT64($handle)
	{
	    $u = unpack("va/vb/vc/vd", fread($handle, 8));
	    return array(0 => $u["a"], 1 => $u["b"], 2 => $u["c"], 3 => $u["d"]);
	}
	
	private function AddUINT64($a, $b)
	{
	    $o = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
	
	    $carry = 0;
	    for ($i = 0; $i < 4; $i++) 
	    {
	        if (($a[$i] + $b[$i] + $carry) > 0xffff ) 
	        {
	            $o[$i] += ($a[$i] + $b[$i] + $carry) & 0xffff;
	            $carry = 1;
	        }
	        else 
	        {
	            $o[$i] += ($a[$i] + $b[$i] + $carry);
	            $carry = 0;
	        }
	    }
	    
	    return $o;   
	}
	
	private function UINT64FormatHex($n)
	{   
	    return sprintf("%04x%04x%04x%04x", $n[3], $n[2], $n[1], $n[0]);
	}
	
	private function filesize64($file)
	{
	    static $iswin;
	    if (!isset($iswin))
	        $iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
	
	    static $exec_works;
	    if (!isset($exec_works))
	        $exec_works = (function_exists('exec') && !ini_get('safe_mode') && @exec('echo EXEC') == 'EXEC');
	
	    // try a shell command
	    if ($exec_works)
	    {
	        $cmd = ($iswin) ? "for %F in (\"$file\") do @echo %~zF" : "stat -c%s \"$file\"";
	        @exec($cmd, $output);
	        if (is_array($output) && ctype_digit($size = trim(implode("\n", $output))))
	            return $size;
	    }
	
	    // try the Windows COM interface
	    if ($iswin && class_exists("COM"))
	    {
	        try {
	            $fsobj = new COM('Scripting.FileSystemObject');
	            $f = $fsobj->GetFile( realpath($file) );
	            $size = $f->Size;
	        } catch (Exception $e) {
	            $size = null;
	        }
	        if (ctype_digit($size))
	            return $size;
	    }
	
	    // if all else fails
	    return filesize($file);
	}	
}
