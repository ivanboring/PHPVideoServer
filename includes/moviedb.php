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
		$parameters['api_key'] = self::$config['moviedb']['apikey'];
		$parameters['query'] = $name;
		$parameters['year'] = date('Y', strtotime($date));
		$data = self::$moviedbQuery->call('3/search/movie', 'GET', null, null, $parameters);
		$return = array();
		if (isset($data['results'][0])) {
			$return['images'] = $data['results'][0]['backdrop_path'];
			$return['poster'] = $data['results'][0]['poster_path'];
		}
		return $return;
	}
}
