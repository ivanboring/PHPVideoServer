<?php

class install extends core
{
	protected function install() {
		$structure = array (
			'settings' => 
			array (
				'analysis' => 
				array (
					'analyzer' => 
					array (
						'description' => 
						array (
							'type' => 'custom',
							'filter' => 
							array (
								0 => 'snowball1367261176Filter',
								1 => 'stop1367261176Filter',
								2 => 'standard',
								3 => 'lowercase',
							),
							'tokenizer' => 'standard',
						),
					),
					'filter' => 
					array (
						'stop1367261176Filter' => 
						array (
							'position_increments' => 'true',
							'type' => 'stop',
							'ignore_case' => 'false',
							'stopwords' => 
							array (
								0 => '_english_',
							),
						),
						'snowball1367261176Filter' => 
						array (
							'language' => 'English',
							'type' => 'snowball',
						),
					),
				),
			),
			'mappings' => 
			array (
				'movie' => 
				array (
					'properties' => 
					array (
						'genre' => 
						array (
							'index' => 'not_analyzed',
							'omit_norms' => true,
							'index_options' => 'docs',
							'type' => 'string',
						),
						'filepath' => 
						array (
							'index' => 'no',
							'type' => 'string',
						),
						'videotype' => 
						array (
							'index' => 'no',
							'type' => 'string',
						),						
						'istv' => 
						array (
							'type' => 'boolean',
						),
						'runtime' => 
						array (
							'type' => 'integer',
						),
						'imdb_id' => 
						array (
							'index' => 'no',
							'type' => 'integer',
						),
						'autocomplete' => 
						array (
							'index' => 'not_analyzed',
							'omit_norms' => true,
							'index_options' => 'docs',
							'type' => 'string',
						),
						'director' => 
						array (
							'index' => 'not_analyzed',
							'omit_norms' => true,
							'index_options' => 'docs',
							'type' => 'string',
						),
						'title' => 
						array (
							'type' => 'multi_field',
							'fields' => 
							array (
								'title' => 
								array (
									'type' => 'string',
								),
								'untouched' => 
								array (
									'include_in_all' => false,
									'index' => 'not_analyzed',
									'omit_norms' => true,
									'index_options' => 'docs',
									'type' => 'string',
								),
							),
						),
						'releaseDate' => 
						array (
							'format' => 'dateOptionalTime',
							'type' => 'date',
						),
						'cast' => 
						array (
							'index' => 'not_analyzed',
							'omit_norms' => true,
							'index_options' => 'docs',
							'type' => 'string',
						),
						'description' => 
						array (
							'analyzer' => 'description',
							'type' => 'string',
						),
						'images' => 
						array (
							'index' => 'no',
							'type' => 'string',
						),
						'poster' => 
						array (
							'index' => 'no',
							'type' => 'string',
						),
						'language' => 
						array (
							'index' => 'not_analyzed',
							'omit_norms' => true,
							'index_options' => 'docs',
							'type' => 'string',
						),
						'tagline' => 
						array (
							'type' => 'string',
						),
					),
				),
			),
		);
		
		self::$query->call(self::$config['movie_index'], 'PUT', json_encode($structure));
		
		$structure = array (
				'mappings' => 
				array (
					'autocomplete' => 
					array (
						'properties' => 
						array (
							'weight' => 
							array (
								'type' => 'integer',
							),
							'untouchedname' => 
							array (
								'index' => 'not_analyzed',
								'omit_norms' => true,
								'index_options' => 'docs',
								'type' => 'string',
							),
							'fullname' => 
							array (
								'analyzer' => 'simple',
								'type' => 'string',
							),
						),
					),
				),
			);
		
		self::$query->call(self::$config['movie_autocomplete'], 'PUT', json_encode($structure));
	
		$structure = array (
				'mappings' => 
				array (
					'episode' => 
					array (
						'properties' => 
						array (
							'filepath' => 
							array (
								'index' => 'not_analyzed',
								'omit_norms' => true,
								'index_options' => 'docs',
								'type' => 'string',
							),
							'parent' => 
							array (
								'index' => 'not_analyzed',
								'omit_norms' => true,
								'index_options' => 'docs',
								'type' => 'string',
							),							
							'title' => 
							array (
								'type' => 'multi_field',
								'fields' => 
								array (
									'title' => 
									array (
										'analyzer' => 'simple',
										'type' => 'string',
									),
									'untouched' => 
									array (
										'include_in_all' => false,
										'index' => 'not_analyzed',
										'omit_norms' => true,
										'index_options' => 'docs',
										'type' => 'string',
									),
								),
							),
							'episode' => 
							array (
								'type' => 'integer',
							),
							'season' => 
							array (
								'type' => 'integer',
							),
							'description' => 
							array (
								'type' => 'string',
							)
						),
					),
				),
			);
			
		self::$query->call(self::$config['tv_episodes'], 'PUT', json_encode($structure));
	}
}
?>