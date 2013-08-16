<?

class page extends core
{
	public function __construct()
	{
		
	}
	
	public function render()
	{
		$q = isset($_GET['q']) ? $_GET['q'] : '';

		$destination = explode('/', $q);
		
		$destination[0] = $destination[0] ? $destination[0] : 'start';
		
		$function = array_shift($destination);

		if (!method_exists($this, $function)) {
			exit;
		}
		
		$this->$function($destination);
	}
	
	private function search($args = array())
	{
		$data['size'] = isset($args[0]) ? $args[0] : 20;
		$data['sort']['title.untouched']['order'] = 'ascending';
		$response = self::$query->call(self::$config['movie_index'] . '/movie/_search', 'POST', json_encode($data));
		echo json_encode($response['hits']);
	}
	
	private function getMovie($args = array())
	{
		$id = isset($args[0]) ? $args[0] : exit;
		$response = self::$query->call(self::$config['movie_index'] . '/movie/' . $id, 'GET');
		if (isset($response['_source'])) {
			echo json_encode($response);
		}
	}
	
	private function style($args = array())
	{
		$style = $args[0];
		if (file_exists('style/' . $style . '.php')) {
			echo json_encode(array('page' => file_get_contents('style/' . $style . '.php')));
		}
	}
	
	private function movie($args = array())
	{
		$movie = new movie();
		$id = isset($args[0]) ? $args[0] : exit;
		$movie->stream($id);
	}
	
	private function image($args = array())
	{
		$image = new image();
		if(!isset($args[2]))
		{
			$args[2] = 'original';
		}
		
		if(count($args) != 3) exit;
		
		$name = $args[0];
		$type = $args[1];
		$size = $args[2];
		
		$image->render($name, $type, $size);
		
	}
	
	private function start()
	{
		$this->outputPage();
	}
	
	private function outputPage($args = array())
	{
		$this->htmlHeader($args);
		$this->htmlPage($args);
		$this->htmlFooter($args);
	}
	
	private function htmlHeader($args)
	{
		echo "<!DOCTYPE html>\n<html>\n<head>\n";
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style.css\">\n";
		echo "<link href=\"http://vjs.zencdn.net/c/video-js.css\" rel=\"stylesheet\">\n";
		echo "<script type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"js/page.js\"></script>\n";
		echo "<script src=\"http://vjs.zencdn.net/c/video.js\"></script>\n";
		echo "<meta charset=\"UTF-8\">\n<title>Videos</title>\n</head>\n\n<body>\n";
	}
	
	private function htmlPage($args)
	{
		echo "<div id=\"main\"></div>\n";
	}
	
	private function htmlFooter($args)
	{
		echo "</body>\n";
	}
}
