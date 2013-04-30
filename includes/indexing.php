<?php

class indexing extends core
{
	private static $opensubtitles = null;
	private static $imdb = null;
	private static $moviedb = null;
	private static $tvdb = null;
	
	public function __construct() 
	{
		
	}
	
	public function index() 
	{
		self::$opensubtitles = new opensubtitles(self::$config['opensubtitles']);
		self::$imdb = new imdb();
		self::$moviedb = new moviedb();
		self::$tvdb = new tvdb();
		
		$getIndexed = $this->getAllIndexed();

		$getFiles = $this->getDirectories();
		
		$this->fileinfo = finfo_open(FILEINFO_MIME_TYPE);
		foreach($getFiles as $entity) {
			if (isset($getIndexed[$entity])) {
				unset($getIndexed[$entity]);
				$entity = null;	
			}
			
			if(strpos(strtolower($entity), 'sample') !== false) $entity = '';
			
			if ($entity) $this->indexEntity($entity);
		}
		finfo_close($this->fileinfo);		
	}
	
	private function indexEntity($entity)
	{
		$entity = $this->getVidExtension($entity);
		$entity = $this->getVidType($entity);
		
		if ($entity) {
			$imdb_id = $this->getImdbId($entity);
			if ($imdb_id) {
				$imdb_id = str_pad($imdb_id, 7, 0, STR_PAD_LEFT);
				$data = self::$imdb->getData($imdb_id);
				$data['filepath'] = $entity;
				$data['imdb_id'] = $imdb_id;
				
				if (isset($data['istv']) && $data['istv']) {
					$this->saveEpisode($data);
				} else {
					$images = self::$moviedb->getImages($data['title'], $data['releaseDate']);
					if (isset($data['genre']) && is_array($data['genre'])) {
						$data = array_merge($data, $images);
						$this->saveMovie($data);
						$this->saveAutocomplete($data);
					} else {
						echo 'No data: ' . $entity . '<br>';
					}
				}
			} else {
				echo 'No imdb: ' . $entity . '<br>';
			}
		}		
	}
	
	private function saveEpisode($data)
	{
		$this->refreshMovies();
		$parent = $this->getTvSeries($data['title']);
		if (!$parent) {
			$parentdata = self::$imdb->getData($data['tvshow_id'], true);
			$images = self::$tvdb->getImages($parentdata['title']);
			$parentdata = array_merge($parentdata, $images);
			$this->saveMovie($parentdata);
			$this->saveAutocomplete($parentdata);
			
			$this->refreshMovies();
			$parent = $this->getTvSeries($data['title']);
		}
		$data['parent'] = $parent;
		$this->saveEpisodeInstance($data);
	}
	
	private function saveEpisodeInstance($data)
	{
		$pattern = '/Season (\d+)\, Episode (\d+)/i';
		preg_match_all($pattern, $data['episode'], $matches);
		$newdata['episode'] = isset($matches[2][0]) ? $matches[2][0] : '';
		$newdata['season'] = isset($matches[1][0]) ? $matches[1][0] : '';
		
		$newdata['title'] = $data['episode_name'];
		$newdata['parent'] = $data['parent'];
		$newdata['description'] = $data['description'];
		$newdata['filepath'] = $data['filepath'];

		self::$query->call(self::$config['tv_episodes'] . '/episode', 'POST', json_encode($newdata));

	}
	
	private function getTvSeries($name)
	{
		$query = array('query' => array('filtered' => array('query' => array('term' => array('title.untouched' => array('value' => $name))), 'filter' => array('term' => array('istv' => true)))));
		
		$exists = self::$query->call(self::$config['movie_index'] . '/movie/_search', 'POST', json_encode($query));

		if (isset($exists['hits']['hits'][0])) {
			return $exists['hits']['hits'][0]['_id'];
		}
		return false;
	}
	
	private function getAllIndexed()
	{
		$fields = array();
		
		$data = self::$query->call(self::$config['movie_index'] . '/movie/_search', 'POST', json_encode(array('query' => array('match_all' => array()))), null, array('fields' => 'filepath'));
		if (isset($data['hits']['hits'])) {
			foreach($data['hits']['hits'] as $hit) {
				if (isset($hit['fields']['filepath'])) {
					$fields[$hit['fields']['filepath']] = $hit['fields']['filepath'];
				}
			}
		}
		
		$data = self::$query->call(self::$config['tv_episodes'] . '/episode/_search', 'POST', json_encode(array('query' => array('match_all' => array()))), null, array('fields' => 'filepath'));
		if (isset($data['hits']['hits'])) {
			foreach($data['hits']['hits'] as $hit) {
				if (isset($hit['fields']['filepath'])) {
					$fields[$hit['fields']['filepath']] = $hit['fields']['filepath'];
				}
			}
		}
		return $fields;
	}
	
	private function saveMovie($data)
	{
		self::$query->call(self::$config['movie_index'] . '/movie', 'POST', json_encode($data));
	}
	
	private function refreshAutocomplete()
	{
		self::$query->call(self::$config['movie_autocomplete'] . '/_refresh', 'GET');
	}
	
	private function refreshMovies()
	{
		self::$query->call(self::$config['movie_index'] . '/_refresh', 'GET');
	}
	
	private function saveAutocomplete($data)
	{
		// Save autocomplete
		$weight = 1;
		foreach($data['autocomplete'] as $value) {
			$this->saveAutocompleteInstance($value, $weight);
		}
		
		// Save language
		$weight = 1;
		foreach($data['language'] as $value) {
			$this->saveAutocompleteInstance($value, $weight);
		}
		
		// Save director
		$weight = 2;
		foreach($data['director'] as $value) {
			$this->saveAutocompleteInstance($value, $weight);
		}
		
		// Save genre
		$weight = 2;
		foreach($data['genre'] as $value) {
			$this->saveAutocompleteInstance($value, $weight);
		}
		
		// Save cast
		$weight = 3;
		foreach($data['cast'] as $value) {
			$this->saveAutocompleteInstance($value, $weight);
		}
								
		// Save title
		$this->saveAutocompleteInstance($data['title'], 100);
	}
	
	private function saveAutocompleteInstance($value, $weight)
	{
		$exists = self::$query->call(self::$config['movie_autocomplete'] . '/_search', 'POST', json_encode(array('query' => array('term' => array('untouchedname' => strtolower($value))))));
		if (isset($exists['hits']['hits'][0])) {
			$id = $exists['hits']['hits'][0]['_id'];
			$weight = $weight + $exists['hits']['hits'][0]['_source']['weight'];
		}
		
		$url = self::$config['movie_autocomplete'] . '/autocomplete';
		if (isset($id)) $url .= '/' . $id;
		
		$data['fullname'] = $value;
		$data['untouchedname'] = strtolower($value);
		$data['weight'] = $weight;
		
		self::$query->call($url, 'POST', json_encode($data));
		$this->refreshAutocomplete();
	}
	
	private function getDirectories() 
	{
		$directories = self::$config['dir'];
		
		$files = array();
		foreach($directories as $dir) {
			$this->getFiles($dir, $files);
		}
		
		return $files;
	}
	
	private function getFiles($path, &$files) 
	{
		$dirs = scandir($path);
		
		foreach($dirs as $dir) {
			if(substr($dir, 0, 1) != '.')
			{
				$entity = $path . '/' . $dir;
				if (is_dir($entity)) {
					$this->getFiles($entity, $files);
				} else {
					$files[] = $entity;
				}
			}
		}
	}
	
	private function getImdbIdFromNfo($file) 
	{
		if (file_exists(substr($file, 0, -3) . 'nfo')) {
			$pattern = '/imdb\.[^\/]+\/title\/tt(\d+)/i';
			preg_match($pattern, file_get_contents(substr($file, 0, -3) . 'nfo'), $matches);
			return $matches[1];
		}
		return null;
	}
	
	private function getImdbId($file)
	{
		$imdb_id = $this->getImdbIdFromNfo($file);
		
		if(!$imdb_id) {
			$name = $this->cleanName(basename($file));
			$imdb_id = self::$opensubtitles->findFile($file, $name);			
		}
		
		return $imdb_id;
	}
	
	private function cleanName($name)
	{
		$name = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $name);
		$name = preg_replace('/\b(www).[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $name);
		$parts = preg_split("/[.]|[ ]|[-]|[_]|[\/]|[\[]|[\]]/", $name);
		$name = implode(' ', array_udiff($parts, self::$config['clean_name'], 'strcasecmp'));
		$name = preg_replace('/(\d{4})/s', "($1)", $name);
		$parts = preg_split('/\((\d{4})\)/s', $name, 0, PREG_SPLIT_DELIM_CAPTURE);
		$name = $parts[0];
		$name .= isset($parts[1]) ? '(' . $parts[1] . ')' : '';
		return $name;
	}
	
	private function getVidExtension($file)	
	{
		switch(substr($file, -3)) {
			case 'mkv':
			case 'avi':
			case 'mp4':
				return $file;
				break;
			default:
				return null;
				break;
		}
	}
	
	private function getVidType($file) 
	{
		if(!$file) return null;
		
		switch(finfo_file($this->fileinfo, $file)) {
			case 'video/x-msvideo':
			case 'video/mp4':
			case 'application/octet-stream':
				return $file;
				break;
			default:
				return null;
				break;
		}
	}
}
