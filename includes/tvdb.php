<?

class tvdb extends core
{
	private static $tvdbQuery = null;
		
	function __construct()
	{
		self::$tvdbQuery = new Query(self::$config['tvdb']['host'], self::$config['tvdb']['port']);
	}
	
	function getImages($name)
	{
		$sanname = $this->sanitize($name);
		$parameters = array('string' => $name, 'tab' => 'listseries', 'function' => 'Search');
		$page = self::$tvdbQuery->call('/', 'GET', null, null, $parameters);

		$pattern = '/<a href\=\"\/\?tab\=series\&amp\;id\=(.*?)\&amp\;lid\=7\">.*? <\/a> <\/td><td class\=\".*?\">English<\/td>/i';
		preg_match_all($pattern, $page, $matches);
		$tvid = isset($matches[1][0]) ? $matches[1][0] : '';	
		
		$parameters = array('tab' => 'series', 'id' => $tvid, 'lid' => 7);
		$page = self::$tvdbQuery->call('/', 'GET', null, null, $parameters);
		
		$pattern = '/<a href\=\"banners\/fanart\/original\/(.*?)\" target\=\"_blank\">View Full Size<\/a>/i';
		preg_match_all($pattern, $page, $matches);
		$image = isset($matches[1][0]) ? $matches[1][0] : '';	

		$pattern = '/<a href\=\"banners\/posters\/(.*?)\" target\=\"_blank\">View Full Size<\/a>/i';
		preg_match_all($pattern, $page, $matches);
		$poster = isset($matches[1][0]) ? $matches[1][0] : '';	
				
		$imageurl = self::$config['tvdb']['host'] . 'banners/fanart/original/' . $image;
		$posterurl = self::$config['tvdb']['host'] . 'banners/posters/' . $poster;
		
		$return['image'] = 'img/' . $sanname . '_image.jpg';
		$return['poster'] = 'img/' . $sanname . '_poster.jpg';

		copy($imageurl, $return['image']);
		copy($posterurl, $return['poster']);
		
		return $return;
	}
}
