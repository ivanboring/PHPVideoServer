<?php

class imdb extends core
{
	private static $imdbQuery = null;
	
	function __construct()
	{
		self::$imdbQuery = new Query(self::$config['imdb']['host'], self::$config['imdb']['port']);
	}
	
	public function getData($id, $tv = false)
	{
		$page = self::$imdbQuery->call(self::$config['imdb']['method'] . $id . '/', 'GET');
		
		$data['title'] = '';
		$data['istv'] = $tv;
		// TV series specific things
		if (strpos($page, 'class="tv_header"') !== FALSE) {
			$pattern = '/<a href\=\"\/title\/tt(.*?)\/.*?\" > (.*?)<\/a>\:/i';
			preg_match_all($pattern, $page, $matches);
			$data['tvshow_id'] = isset($matches[1][0]) ? $matches[1][0] : array();
			$data['title'] = isset($matches[2][0]) ? $matches[2][0] : array();
			
			$pattern = '/\: \n        <span class\=\"nobr\">(.*?)\n        <\/span>/i';
			preg_match_all($pattern, $page, $matches);			
			$data['episode'] = isset($matches[1][0]) ? $matches[1][0] : array();
			
			$pattern = '/<\/h2>\n<h1 class\=\"header\"> <span class\=\"itemprop\" itemprop\=\"name\">(.*?)<\/span>/i';
			preg_match_all($pattern, $page, $matches);			
			$data['episode_name'] = isset($matches[1][0]) ? $matches[1][0] : array();
			
			$data['istv'] = true;
		}
		
		// Get genre
		$pattern = '/<span class\=\"itemprop\" itemprop\=\"genre\">(.*)<\/span><\/a>/i';
		preg_match_all($pattern, $page, $matches);
		$data['genre'] = isset($matches[1]) ? $matches[1] : array();
		
		// Get releaseDate
		$pattern = '/<meta itemprop\=\"datePublished\" content\=\"(.*)\" \/\>/i';
		preg_match_all($pattern, $page, $matches);
		$data['releaseDate'] = isset($matches[1][0]) ? $matches[1][0] : '';

		// Get runtime
		$pattern = '/<time itemprop\=\"duration\" datetime\=\".*?\">(.*?) min<\/time>/i';
		preg_match_all($pattern, $page, $matches);
		$data['runtime'] = isset($matches[1][0]) ? $matches[1][0] : '';

		// Get languages
		$pattern = '/<a href\=\"\/language\/.*?\" .*?>(.*?)<\/a>/i';
		preg_match_all($pattern, $page, $matches);
		$data['language'] = isset($matches[1]) ? $matches[1] : array();
		
		// Get director
		$pattern = '/<h4 class\=\"inline\">Director:<\/h4>\n<a.*?><span class\=\"itemprop\" itemprop\=\"name\">(.*?)<\/span>/i';		
		preg_match_all($pattern, $page, $matches);
		$data['director'] = isset($matches[1]) ? $matches[1] : array();
		
		// Get tagline
		$pattern = '/<h4 class\=\"inline\">Taglines:<\/h4>\n(.*?)<span class\=\"see-more inline\">/i';		
		preg_match_all($pattern, $page, $matches);
		$data['tagline'] = isset($matches[1][0]) ? trim($matches[1][0]) : '';
				
		// Get description
		$page = self::$imdbQuery->call(self::$config['imdb']['method'] . $id . '/plotsummary?', 'GET');
		$pattern = '/<p class\=\"plotpar\">(.*?)<i>/s';
		preg_match_all($pattern, $page, $matches);
		$data['description'] = isset($matches[1][0]) ? $matches[1][0] : '';		

		// Get tags
		$page = self::$imdbQuery->call(self::$config['imdb']['method'] . $id . '/keywords', 'GET');
		$pattern = '/<td><a href\=\"\/keyword\/.*?" >(.*?)<\/a><\/td>/i';
		preg_match_all($pattern, $page, $matches);
		$data['autocomplete'] = isset($matches[1]) ? array_map('strtolower', $matches[1]) : array();		
		
		// Get cast
		$page = self::$imdbQuery->call(self::$config['imdb']['method'] . $id . '/fullcredits', 'GET');

		if ($tv) {
			$pattern = '/<img src\=.*?><\/a><br><\/td><td class\=\"nm\"><a href\=\"\/name\/.*?\">(.*?)<\/a><\/td>/i';
		} else {
			$pattern = '/<td class\=\"nm\"><a href\=\"\/name\/.*?\/\" onclick\=\".*?\">(.*?)<\/a><\/td>/i';
		}

		preg_match_all($pattern, $page, $matches);
		$data['cast'] = isset($matches[1]) ? array_map('strtolower', $matches[1]) : array();		

		// Get character names
		$pattern = '/<td class\=\"char\">(.*?)<\/td>/i';
		preg_match_all($pattern, $page, $matches);
		foreach($matches[1] as $match) {
			if (strpos($match, 'Himself') === false) {
				$match = strtolower(str_replace('&#160;/ ...', '', preg_replace('/\((\d+) episodes, (\d+)\)/i', '', preg_replace('/\((\d+) episodes, (\d+)\-(\d+)\)/i', '', strip_tags($match)))));	
				$data['autocomplete'][] = $match;
			}
		}

		// Get name
		$page = self::$imdbQuery->call(self::$config['imdb']['method'] . $id . '/releaseinfo', 'GET');
		$pattern = '/<a class\=\"main\" href=\"\/title\/.*?\/\">(.*?)<\/a> <span>/i';
		preg_match_all($pattern, $page, $matches);
		$data['title'] = isset($matches[1][0]) && !$data['title'] ? trim($matches[1][0], '&#x22;') : $data['title'];
		
		// Get alternative names
		$pattern = '/<tr>\n<td>(.*?)<\/td>/i';
		preg_match_all($pattern, $page, $matches);
		foreach($matches[1] as $match) {
			$data['autocomplete'][] = strip_tags($match);	
		}		

		return $data;			
	}
}
