<?

class moviedb extends core
{
	private static $moviedbQuery = null;
		
	function __construct()
	{
		self::$moviedbQuery = new Query(self::$config['moviedb']['host'], self::$config['moviedb']['port']);
	}
	
	function getImages($name, $date)
	{
		$sanname = $this->sanitize($name);
		
		$parameters['api_key'] = self::$config['moviedb']['apikey'];
		$parameters['query'] = $name;
		$parameters['year'] = date('Y', strtotime($date));
		$data = self::$moviedbQuery->call('3/search/movie', 'GET', null, null, $parameters);
		$return = array();
		if (isset($data['results'][0])) {
	
			$imageurl = 'http://d3gtl9l2a4fn1j.cloudfront.net/t/p/original/' . $data['results'][0]['backdrop_path'];
			$posterurl = 'http://d3gtl9l2a4fn1j.cloudfront.net/t/p/original/' . $data['results'][0]['poster_path'];
			
			$return['image'] = 'img/' . $sanname . '_image.jpg';
			$return['poster'] = 'img/' . $sanname . '_poster.jpg';

			copy($imageurl, $return['image']);
			copy($posterurl, $return['poster']);
		}
		return $return;
	}
}
