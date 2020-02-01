<?php
use \Exception;

/**
 * FilesUpload - upload multiple files from from
 * 
 * Html form or from curl
 * <form enctype="multipart/form-data" method="post">
 * <input type="file" name="files[]" multiple>
 * <input type="submit" value="Send">
 * </form>
 */
class FilesUpload
{
  protected $AllowedExtensions = array();
  protected $FilesJson = array();
  protected $InputFileName = "files";
  protected $UploadPath = "/media/files";
  protected $MaxFileSizeBytes = 1024000;
  // Disable authentication
  protected $Authenticate = false;
  // Credentials
  protected $Username = "";
  protected $Password = "";

  function __construct(){
    // Enable errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
  }

  /**
   * Compare credentials from POST request
   * $_POST['upload_user']
   * $_POST['upload_pass']
   *
   * @return bool true or false
   */
  function IsValidCredentials(){
    if($this->Username == $_POST['upload_user'] && $this->Password == $_POST['upload_pass']){
      return true;
    }
    return false;
  }

  /**
   * Set credentials
   *
   * @param string $user
   * @param string $pass
   * @return void
   */
  function SetCredentials($user, $pass){
    $this->Username = $user;
    $this->Password = $pass;
  }

  function SetAuthenticate(){
    $this->Authenticate = true;
  }

  function AddExtension($ext = ""){
    if(is_array($ext)){
      foreach ($ext as $k => $v) {
        $this->AllowedExtensions[] = $v; 
      }  
    }else{
      $this->AllowedExtensions[] = $ext; 
    }
  }

  function AddUploadPath($path = "/media/files"){
    $path = rtrim($path,'/');    
    $this->UploadPath = $path;    
  }

  function CreateUploadFolder($folder = ""){
    $folder = rtrim($folder,'/');        
    mkdir($this->UploadPath.'/'.$folder, 0777, true);    
    if(!file_exists($this->UploadPath.'/'.$folder)){
      throw new Exception("Error folder upload path does not exists", 9000);
    }
    return $this->UploadPath.'/'.$folder;    
  }

  /**
   * Change $_FILES input file name
   * 
   * @param string $name Change upload form name $_FILES[$name]
   * @return void
   */
  function SetInputFileName($name = "files"){
    $this->InputFileName = $name;
  }

  /**
   * Max file size in bytes
   *
   * @param integer $bytes
   * @return void
   */
  function SetMaxFileSize($bytes = 1024000){
    if($bytes > 0){
      $this->MaxFileSizeBytes = $bytes;
    }
  }

  /**
   * Random unique id
   *
   * @return string
   */
  function UniqueId(){
    return uniqid().time();
  }

  /**
   * Get file extension
   *
   * @param string $path Set path to file
   * @return string Return file extension
   */
  function GetExtension($path){
    return strtolower(pathinfo($path, PATHINFO_EXTENSION));    
  }

  /**
   * Upload all files to directory
   * from $_FILES['files']
   *
   * @return string Json files list
   */
  function Upload(){
    $this->RemoveEmptySubFolders($this->UploadPath);
    // Authentication
    if($this->Authenticate == true){
      if(!$this->IsValidCredentials()){
        throw new Exception("Error Authenticate first", 9004);
      }
    }
    if(empty($this->AllowedExtensions)){
      throw new Exception("Error Add allowed extensions first: AddExtension('jpg')", 9001);
    }
    if(!empty($_FILES[$this->InputFileName]['name'])) {   
      $cnt = count($_FILES[$this->InputFileName]['name']);
      for($key = 0; $key < $cnt; $key++) {
        // Check file
        if(!empty($_FILES[$this->InputFileName]['name'][$key]) && $_FILES[$this->InputFileName]['size'][$key] > 0 && $_FILES[$this->InputFileName]['size'][$key] < $this->MaxFileSizeBytes) {
          $file = $_FILES[$this->InputFileName]['name'][$key];
          $ext = $this->GetExtension($file);
          $uid = $this->UniqueId();
          $folder = $this->CreateUploadFolder($uid);
          // test extensions
          if(in_array($ext, $this->AllowedExtensions)){             
            $target = $folder .'/'. $uid .'.'.$ext;
            $tmp  = $_FILES[$this->InputFileName]['tmp_name'][$key];
            // Upload
            $ok = move_uploaded_file($tmp, $target);
            if($ok){
              $this->FilesJson[] = array($file => $target);
            }
          }else{
            throw new Exception("Error file extension: " . implode(", ", $this->AllowedExtensions), 9002);
          }
        }else{
          throw new Exception("Error file size or path", 9003);
        }
      }
    }
    return json_encode($this->FilesJson);
  }

  function RemoveEmptySubFolders($path){
    foreach (glob($path.'/'."*") as $file){
      if(is_dir($file)){
        rmdir($file);
      }
    }
  }

  function ShowFilesArray(){
    print_r($_FILES[$this->InputFileName]);
  }

  function ShowExtensions(){
    print_r($this->AllowedExtensions);
  }
}

/*
try{
  
  $f = new FilesUpload();  

  // Enable authentication
  $f->SetAuthenticate();
  // Set username -> $_POST['upload_user'] and password -> $_POST['upload_pass']
  $f->SetCredentials("username","pass");

  // Allowed files extsensions
  $f->AddExtension('jpg');
  $f->AddExtension('png');
  
  // Max upload size bytes
  $f->SetMaxFileSize(5024000);
  
  // Upload folder
  $f->AddUploadPath("media/files");
  
  // uploaded files list
  echo $filesList = $f->Upload();

  // Show $_FILES
  // $f->ShowFilesArray();
}catch(Exception $e){
  print_r($e);
}
*/
?>
