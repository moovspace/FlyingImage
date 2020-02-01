<?php
/**
 * FlyingImage class
 */
class FlyingImage
{
	// Error image
	public $errorImg = 'media/img/logo.png';
	// Allowed image size
	public $thumbSize = array(256,512,768,1366);

	/**
	 * resizeImage Resize image if exist else show errorImg image with width 256px
	 * @param  string  $file     Image path
	 * @param  integer $maxwidth Resize image if width bigger than $maxwidth
	 * @param  integer $create   Create thumbnail, set to 1
	 * @param  integer $load     Load thumbnail image if exist
	 * @param  integer $verify   Test is correct folder path
	 * @return image             Display image content in browser
	 */
	function resizeImage($file = 'image.jpg', $maxwidth = 512, $create = 0, $load = 0, $verify = 0){
		$file = ltrim($file,'/');

		// Set thumb size
		$maxwidth = $this->dynmicSize($maxwidth,$this->thumbSize);

		if($verify == 1){
			// If folder allowed
			if(!$this->allowedPath($file)){
				$this->showImage($this->errorImg);
			}
		}

		// File does not exists, display errorImg
		if(!file_exists($file)){
			$this->showImage($this->errorImg);
		}

		// Only jpg,jpeg,png,gif
		if(!$this->allowedExtension($file)){
			$this->showImage($this->errorImg);
		}

		// Load thumbnai if exist
		if($load == 1){
			$f = $this->getThumbName($file, $maxwidth);
			if(file_exists($f)){
				$this->showImage($f);
			}
		}

		ob_end_clean();
		$image_info = getimagesize($file);
		$image_width = $image_info[0];
		$image_height = $image_info[1];
		$ratio = $image_width / $maxwidth;
		$info = getimagesize($file);
		if ($image_width > $maxwidth) {
			// Cache
			$this->setCache();
			// Change size
			$newwidth = $maxwidth;
			$newheight = (int)($image_height / $ratio);
			if ($info['mime'] == 'image/jpeg') {
				header('Content-Type: image/jpeg');
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				$source = imagecreatefromjpeg($file);
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				imagejpeg($thumb,NULL,90);
				if($create == 1){
					imagejpeg($thumb,$this->getThumbName($file, $maxwidth),90);
				}
			}
			if ($info['mime'] == 'image/jpg') {
				header('Content-Type: image/jpeg');
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				$source = imagecreatefromjpeg($file);
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				if($create == 1){
					imagejpeg($thumb,$this->getThumbName($file, $maxwidth),90);
				}
				imagejpeg($thumb,NULL,90);
			}
			if ($info['mime'] == 'image/png') {
				header('Content-Type: image/png');
				$im = imagecreatefrompng($file);
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				imagealphablending($thumb, false);
				imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				imagesavealpha($thumb, true);
				if($create == 1){
					imagepng($thumb,$this->getThumbName($file, $maxwidth),9); // from 0 -> 9
				}
				imagepng($thumb, NULL, 9);
			}
			if ($info['mime'] == 'image/gif') {
				header('Content-Type: image/gif');
				$im = imagecreatefromgif($file);
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				imagealphablending($thumb, false);
				imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				imagesavealpha($thumb, true);
				if($create == 1){
					imagegif($thumb,$this->getThumbName($file, $maxwidth));
				}
				imagegif($thumb, NULL);
			}
			imagedestroy($thumb);
			exit;
      	}else{
      		// if width smaller show image don't resize
      		if(file_exists($file)){
				$this->showImage($file);
			}
      	}
    }

    function errorImage($path){
    	$path = ltrim($path,'/');
    	if($this->allowedExtension($path) && file_exists($path)){
	    	$this->errorImg = $path;
	    }else{
	    	$this->errorImg = 'media/img/logo.png';
	    }
    }

    /**
     * convertPngToJpg Png with white background
     * @param  [type] $filePath [description]
     * @return [type]           [description]
     */
    function convertPngToJpg($filePath){
    	$filePath = ltrim($filePath,'/');
		$image = imagecreatefrompng($filePath);
		// $filePath = pathinfo($filePath, PATHINFO_FILENAME);
		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		$filePath = str_replace('.'.$ext, '', $filePath);
		$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
		imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
		imagealphablending($bg, TRUE);
		imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($image);
		$quality = 100; // 0 = worst / smaller file, 100 = better / bigger file
		imagejpeg($bg, $filePath . ".jpg", $quality);
		imagedestroy($bg);
		return $filePath . ".jpg";
    }

    /**
     * [allowedExtension description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    function allowedExtension($file){
    	$ext = pathinfo($file, PATHINFO_EXTENSION);
    	return in_array($ext, array("jpg","jpeg","png","gif"));
    }

    /**
     * [getThumbName description]
     * @param  [type] $file [description]
     * @param  [type] $size [description]
     * @return [type]       [description]
     */
    function getThumbName($file, $size){
    	$file = ltrim($file,'/');
    	$a = explode('.', $file);
    	return $a[0].'-thumb-'.$size.'.'.$a[1];
    }

    /**
     * [showImage description]
     * @param  [type] $f [description]
     * @return display image in browser
     */
    function showImage($f){
		$ext = pathinfo($f, PATHINFO_EXTENSION);
		switch( $ext ) {
		    case "gif": $ctype="image/gif"; break;
		    case "png": $ctype="image/png"; break;
		    case "jpeg":
		    case "jpg": $ctype="image/jpeg"; break;
		    default:
		}
		ob_end_clean();
		$this->setCache(); // Cache
		header('Content-type: ' . $ctype);
		echo file_get_contents($f);
		exit;
    }

    /**
     * [setCache description]
     * @param integer $seconds [description]
     */
    function setCache($seconds = 7200){
		$ts = gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT";
		header("Expires: $ts");
		header("Pragma: cache");
		header("Cache-Control: max-age=$seconds");
    }

    /**
     * [dynmicSize description]
     * @param  integer $size    [description]
     * @param  array   $allowed [description]
     * @return [type]           [description]
     */
    function dynmicSize($size = 256, $allowed = array(256,512,768)){
    	foreach ($allowed as $v) {
    		if($size >= $v){ $s = (int)$v; }
    	}
    	if($s < $allowed[0]){ $s = (int)$allowed[0]; }
    	if($s <= 0){ $s = 256; }
    	return $s;
    }

    /**
     * [setAllowedSize description]
     * @param array $arr [description]
     */
    function setAllowedSize($arr = array(256,512,768)){
    	if(!empty($arr)){
    		foreach ($arr as $k => $v) {
    			$arr[$k] = (int)$v;
    		}
    	}
    	if(!empty($arr)){
	    	$this->thumbSize = array_unique($arr);
	    }else{
	    	$this->thumbSize = array(256,512,768);
	    }
    }

    function setAllowedPaths($arr = array('media/img','media/cdn/2019/')){
    	foreach ($arr as $k => $v) {
			$arr[$k] = rtrim(ltrim($v,'/'),'/');
		}
		$this->allowedPaths = $arr;
    }

    function allowedPath($path){
    	$path = pathinfo($path,PATHINFO_DIRNAME);
    	$path = ltrim($path,'/');
    	$path = rtrim($path,'/');
    	return in_array($path, $this->allowedPaths);
    }
}
?>
