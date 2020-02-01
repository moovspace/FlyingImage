# Flying Image
Resize images on the fly with php

```php
<?php
// Include
require('FlyingImage.php');

// Create object
$fi = new FlyingImage();

// Set allowed thumbnail size (if size > 512 then size => 512)
$fi->setAllowedSize(array(150,256,384,512));

// Set error image
$fi->errorImage('media/img/logo.png');

// Show Image (don't save thumbnail)
$fi->resizeImage($image,$size);

// Show Image (save thumbnail)
$fi->resizeImage($image,$size,1);

// Show Image (display thumbnail if exists)
$fi->resizeImage($image,$size,0,1);

// Show Image (display thumbnail if exists else create thumbnail image)
$fi->resizeImage($image,$size,1,1);

```
### Test image directory paths
```php
// Set allowed paths
$fi->setAllowedPaths($arr = array('/media/img','/media/folder/2019/'));

// Then enable verification (default: disabled)
$fi->resizeImage($image,$size,0,0,1);
```

### File example cdn.php
```php
<?php
// Image path: media/folder/file/image.jpg
$image = $_GET['image'];

// Image size (256,512,640,768,1366,1920)
$size = $_GET['size'];

require('FlyingImage.php');
$fi = new FlyingImage();
$fi->errorImage('media/img/logo.png');
$fi->setAllowedSize(array(256,512,768));
$fi->resizeImage($image,$size);
?>
```

### Usage (index.php)
```html
<style type="text/css">
.image{
    width: 256px; 
    height: 256px; 
    margin: 10px; 
    padding: 5px; 
    border: 1px dashed #f50; 
    object-fit: cover; 
    object-position: center;
    box-sizing: border-box;
}
</style>
<img src="cdn.php?image=media/folder/image.png&size=512" class="image">
```
