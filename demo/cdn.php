<?php
/** 
 * Url in browser
 * http://localhost/demo/cdn.php?image=img/foto.jpg&size=512
 */

$image = $_GET['image'];
$size = $_GET['size'];

require('../FlyingImage.php');
$fi = new FlyingImage();
// Erro image
$fi->errorImage('img/what.png');
// Thumb sizes
$fi->setAllowedSize(array(256,512,768));
// Resize and display
$fi->resizeImage($image,$size);
?>
