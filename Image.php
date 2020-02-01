<?php
/**
 * Image class
 *
 * Crop, convert, resize image
 */
class Image
{
	protected $ImagePath = "";
	protected $ImageExtension = "";
	protected $ImageName = "";
	protected $AllowedExtensions = array('jpg','jpeg','png','gif');

	function __construct(){}

	function AddImage($path)
	{
		if( file_exists($path) && $this->IsValidExtension($path) ){
			$this->ImagePath = $path;
			$this->ImageExtension = $this->GetExtension($path);
			$this->ImageName = $this->GetFileName($path);
			$this->ImageDirectory = $this->GetDirectory($path);
		}else{
			throw new Exception("Error: Image path", 9000);
		}
	}

	/**
	 * Convert function
	 *
	 * Convert image function
	 * @param string $destPath Path to image with extension: jpg, png, gif
	 * @return void
	 */
	function Convert($destPath)
	{
		$this->Crop($destPath);
	}

	/**
	 * Crop function
	 *
	 * Crop or convert image (if you set only $destPath you can convert image to .png, .jpg, .gif)
	 * @param string $destPath File path
	 * @param integer $width Crop width size
	 * @param integer $height Crop height size
	 * @param integer $widthStart Crop from width
	 * @param integer $heightStart Crop from height
	 * @return void
	 */
	function Crop($destPath = "", $width = 0, $height = 0, $widthStart = 0, $heightStart = 0)
	{
		$this->ImageCreate($this->ImagePath);
		$this->ImageWidth = imagesx($this->Image);
		$this->ImageHeight = imagesy($this->Image);
		if($width > $this->ImageWidth || $width <= 0){
			$width = $this->ImageWidth;
		}
		if($height > $this->ImageHeight || $height <= 0){
			$height = $this->ImageHeight;
		}
		if($widthStart < 0 || $widthStart > $this->ImageWidth ){ $widthStart = 0; }
		if($heightStart < 0 || $heightStart > $this->ImageHeight ){ $heightStart = 0; }
		$img = imagecrop($this->Image, ['x' => $widthStart, 'y' => $heightStart, 'width' => $width, 'height' => $height]);
		if ($img !== FALSE) {
			if(empty($destPath)){
				$newpath = $this->ImageDirectory.'/'.$this->ImageName.'-cropped.'.$this->ImageExtension;
			}else{
				$newpath = $destPath;
			}
			$this->ImageSave($img, $newpath);
		}
		imagedestroy($this->Image);
	}

	function IsValidExtension($path)
	{
		return in_array($this->GetExtension($path), $this->AllowedExtensions);
	}

	function ImageCreate($path){
		$ext = $this->GetExtension($path);
		switch ($ext) {
			case 'gif':
				$this->Image = imagecreatefromgif($path);
				break;

			case 'png':
				$this->Image = imagecreatefrompng($path);
				break;

			default:
				$this->Image = imagecreatefromjpeg($path);
				break;
		}
	}

	/**
	 * ImageSave function
	 *
	 * Save image resource to file
	 * @param resorce $img From php function imagecrop()
	 * @param string $path Path to new file or empty (then add: -cropped string to image filename)
	 * @return void
	 */
	function ImageSave($img, $path){
		$this->Saved = false;
		$ext = $this->GetExtension($path);
		$dir = $this->GetDirectory($path);

		if(!file_exists($dir)){
			mkdir($dir,0777,true);
			chmod($dir,0777);
		}

		switch ($ext) {
			case 'gif':
				$this->Saved = imagegif($img, $path);
				imagedestroy($img);
				break;

			case 'png':
				$this->Saved = imagepng($img, $path);
				imagedestroy($img);
				break;

			default:
				$this->Saved = imagejpeg($img, $path, 100);
				imagedestroy($img);
				break;
		}
		if($this->Saved == false){
			throw new Exception("Error: Cant save the file in this location", 9001);
		}
		chmod($path, 0777); // Allow delete
	}

	/**
	 * ConvertPngToJpg function
	 *
	 * Convert png to jpg with white background
	 * @param  string $filePath File path
	 * @return string File path
	 */
	function ConvertPngToJpg($filePath){
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
	 * Resize function
	 *
	 * Resize image
	 * @param string $destPath Destination file path
	 * @param integer $width New image width ($width > 0)
	 * @param integer $height New image height, ($height = 0  - Auto height)
	 * @return void
	 */
	function Resize($destPath, $width = 1366, $height = 0){
		$file = $this->ImagePath;
		$image_info = getimagesize($file);
		$image_width = $image_info[0];
		$image_height = $image_info[1];
		$ratio = $image_width / $width;
		// File info, mime, size
		$info = getimagesize($file);
		if ($image_width > $width) {
			if($width <= 0){ $width = 100; }
			$newwidth = $width;
			$newheight = $height;
			// Auto height
			if($height <= 0){
				$newheight = (int)($image_height / $ratio);
			}

			if ($info['mime'] == 'image/jpeg') {
				$im = imagecreatefromjpeg($file);
				$im_dest = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresized($im_dest, $im, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				imagejpeg($im_dest,$destPath,100);
				imagedestroy($im);
				imagedestroy($im_dest);
			}
			if ($info['mime'] == 'image/png') {
				$im = imagecreatefrompng($file);
				$im_dest = imagecreatetruecolor($newwidth, $newheight);
				imagealphablending($im_dest, false);
				imagecopyresampled($im_dest, $im, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				imagesavealpha($im_dest, true);
				imagepng($im_dest, $file, 9);
				imagedestroy($im);
				imagedestroy($im_dest);
			}
			if ($info['mime'] == 'image/gif') {
				$im = imagecreatefromgif($file);
				$im_dest = imagecreatetruecolor($newwidth, $newheight);
				imagealphablending($im_dest, false);
				imagecopyresampled($im_dest, $im, 0, 0, 0, 0, $newwidth, $newheight, $image_width, $image_height);
				imagesavealpha($im_dest, true);
				imagegif($im_dest, $destPath);
				imagedestroy($im);
				imagedestroy($im_dest);
			}
			// Allow delete
			if(file_exists($destPath)){
				chmod($destPath, 0777);
			}
		}
	}

	/**
	 * GetExtension
	 * Get file extension
	 *
	 * @param string $path Set path to file
	 * @return string Return file extension
	 */
	function GetExtension($path){
		return strtolower(pathinfo(basename($path), PATHINFO_EXTENSION));
	}

	/**
	 * GetName
	 * Get file name
	 *
	 * @param string $path Set path to file
	 * @return string Return file name without extensions
	 */
	function GetFileName($path, $tolower = 1)
	{
		if($tolower == 1){
			return strtolower(pathinfo(basename($path), PATHINFO_FILENAME));
		}else{
			return pathinfo(basename($path), PATHINFO_FILENAME);
		}
	}

	/**
	 * GetDirectory function
	 *
	 * @param string $path File path
	 * @return string Directory path (/path/to/file/directory)
	 */
	function GetDirectory($path)
	{
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * GetInfo function
	 *
	 * Get file info array
	 * @param string $path File path
	 * @return array File info array
	 */
	function GetInfo($path)
	{
		return getimagesize($path);
	}

	/**
	 * GetMime function
	 *
	 * Get file mime type
	 * @param string $path File path
	 * @return string Mime type (e.g. image/jpeg)
	 */
	function GetMime($path)
	{
		$file = $this->GetInfo($path);
		return $file['mime'];
	}
}

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// For errors
use \Exception;

try{
	// create object
	$i = new Image();

	// Add Path to image
	$i->AddImage('media/img.jpg');

	// Resize image
	$i->Resize('filename-thumb.jpg',100);

	// Convert image to (jpg, png, gif)
	$i->Convert('filename.gif');

	// Crop image from 0,0 to 300,300
	$i->Crop('filename.jpg', 300, 300);

	// Crop image from 100,100 to 300,300
	$i->Crop('filename.jpg',300,300,100,100);

	// Show image html
	echo '<img src="filename.jpg">';
	echo '<img src="filename-thumb.jpg">';

}catch(Exception $e){
	print_r($e);
}
*/
?>
