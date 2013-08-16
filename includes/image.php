<?php

class image extends core
{
	public function __construct() 
	{
		
	}
	
	public function render($id, $type, $size = 'original')
	{
		header('Content-Type: image/jpg');
		
		if ($size != 'original') {
			$sizes = explode('x', $size);
		}
		
		$dir = 'img/' . substr($id, 0, 2) . '/' . substr($id, 0, 3) . '/' . $id . '/';
		
		$filename = $type . '.jpg';
		
		$file = $dir . $filename;
		
		//Resizing
		if (isset($sizes) && is_array($sizes) && count($sizes) == 2) {
			$sizedfile = $dir . $type . '_' . $size . '.jpg';
			
			if (file_exists($sizedfile)) {
				echo file_get_contents($sizedfile);
				exit;
			}		
			
			$img_size = getimagesize($file);

			if ($img_size[0] == $sizes[0] && $img_size[1] == $sizes[1]) {
				echo file_get_contents($file);
				exit;	
			};
			
			$widthratio = $sizes[0]/$img_size[0];
			$heightratio = $sizes[1]/$img_size[1];

			if ($widthratio > $heightratio) {
				// Scale on width
				$width = $sizes[0];
				$height = round($img_size[1] * ($sizes[0] / $img_size[0]), 0);
				$new_image = imagecreatetruecolor($width, $height);
				$source_image = imagecreatefromjpeg($file);
				imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $width, $height, $img_size[0], $img_size[1]);
				imagejpeg($new_image, $sizedfile, 100);
			} else {
				// Scale on height
				$width = round($img_size[0] * ($sizes[1] / $img_size[1]), 0);
				$height = $sizes[1];
				$new_image = imagecreatetruecolor($width, $height);
				$source_image = imagecreatefromjpeg($file);
				imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $width, $height, $img_size[0], $img_size[1]);
				imagejpeg($new_image, $sizedfile, 100);							
			}

			if ($width != $sizes[0]) {
				$img_size = getimagesize($sizedfile);
				$new_image = imagecreatetruecolor($sizes[0], $sizes[1]);
				$source_image = imagecreatefromjpeg($sizedfile);
				$newwidth = round(($img_size[0]-$sizes[0])/2);
				imagecopyresampled($new_image, $source_image, 0, 0, $newwidth, 0, $sizes[0]+$newwidth, $sizes[1], $img_size[0], $img_size[1]);
				imagejpeg($new_image, $sizedfile, 80);
			}
			
			if ($height != $sizes[1]) {
				$img_size = getimagesize($sizedfile);
				$new_image = imagecreatetruecolor($sizes[0], $sizes[1]);
				$source_image = imagecreatefromjpeg($sizedfile);
				$newheight = round(($img_size[1]-$sizes[1])/2);
				imagecopyresampled($new_image, $source_image, 0, 0, 0, $newheight, $sizes[0], $sizes[1]+$newheight, $img_size[0], $img_size[1]);
				imagejpeg($new_image, $sizedfile, 80);
			}
			
			if (file_exists($sizedfile)) {
				echo file_get_contents($sizedfile);
				exit;
			}	
		}

		if (file_exists($file)) {
			echo file_get_contents($file);
			exit;
		}	
	}
}
?>