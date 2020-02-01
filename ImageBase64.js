// get image from 
// <input type="file" name="file id="input">
var files = $('#input')[0].files;

// Count files
var len = files.length;

// Get blob for <img src="...">
var url = window.URL.createObjectURL(files[0]);

// Convert and set to textarea with id <textarea id="base64">
getBase64(files[0],'base64');

// Function
function getBase64(file, id) {
   var reader = new FileReader();
   reader.readAsDataURL(file);
   reader.onload = function () {
     console.log(reader.result);     
     document.getElementById(id).value = reader.result;
   };
   reader.onerror = function (error) {
     console.log('Error: ', error);
     document.getElementById(id).value = '';
   };
}
