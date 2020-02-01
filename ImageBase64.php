<?php
class ImageBase64
{	
	// Upload foto to
	public $uploadFolder = 'media/cdn';
	public $dateFolderOn = false;
	public $maxUploadSizeMB = 5;
	private $dir = '';
  
        // Folder format from date 'Y-m-d' -> 2019-01-01, 'Y-m' -> 2019-01, 'Y' -> 2019
	function __construct($dateformat = 'Y'){
		// Current date
    		$format = preg_replace('/[^a-zA-Z\-]/','',$dateformat);
		$day = date($format, time());
		// Unique folder name
		$udir = md5(uniqid().microtime());
		// Upload dir
		$this->dir = $this->uploadFolder.'/'.$day.'/'.$udir;
		// Create folders recursive
		if ( !is_dir( $this->dir ) ) {
			mkdir($this->dir, 0777, true);
		}
	}

	/**
	 * Save file to folder
	 * @param  string $b64 base64 image string: 
	 * data:image/png;base64,... 
	 * data:image/jpeg;base64,... 
	 * data:image/gif;base64,...
	 * @param  string $path file path or empty then unique id will be generated
	 * @return string       image path or <= 0
	 */
	function addBase64($b64 = '',$name = ''){
		// Size in bytes
		$maxSize = $this->maxUploadSizeMB * (1024 * 1024);
		
		// Max file size
		if($this->getImageSize64($b64) > $maxSize){
			// throw new Exception("Error file size", 1);
			return -1;			
		}	

		// test directory path data:image/png;base64
		if(file_exists($this->dir) > 0){

			$itype = array('jpeg','png','gif');
			foreach ($itype as $typ) {
				if (strpos($b64, 'image/'.$typ) > 0) {
					$ext = str_replace('e', '', $typ); // jpeg to jpg
					if(strlen($ext) == 3){
						$name = preg_replace('/[^a-zA-Z0-9]/', '', $name);
						if(empty($name)){
							$fname = $this->dir.'/'.md5(uniqid().microtime()).'.'.$ext;
						}else{
							$fname = $this->dir.'/'.$name.'.'.$ext;
						}
						file_put_contents($fname, base64_decode(str_replace('data:image/'.$typ.';base64,', '', $b64)));
					}
				}
			}

			// 1 - gif, 2 - jpg, 3 - png
			if(file_exists($fname) == 1 && exif_imagetype($fname) >= 1 && exif_imagetype($fname) <= 3){			
				return $fname;
			}else{
				// throw new Exception("Error save file", 1);				
				return 0;
			}

		}else{
			// throw new Exception("Error directory", 1);
			return 0;
		}		
	}

	function maxSize($mb = 5){
		$this->maxUploadSizeMB = (int)$mb;
		if($this->maxUploadSizeMB <= 0){
			$this->maxUploadSizeMB = 5;
		}
		return $this->maxUploadSizeMB;
	}

	function getImageSize64($b64){		
		// bytes
		return (int) (strlen(rtrim($b64, '=')) * 3 / 4);
		// return getimagesizefromstring($b64);
	}

	function getImageSize($path){
		return getimagesize($path);
	}

	function validImage($path){
		return exif_imagetype($path);
	}
}


// Image base 64 to file
/*
// Image base64 (from POST)
echo $b64 = 'data:image/png;base64,'.base64_encode(file_get_contents('media/img/logo.png'));

// Create object
$f = new ImageBase64();	

// Return image path
$imagepath = $f->addBase64($b64);
*/
?>
