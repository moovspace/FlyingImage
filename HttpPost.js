/**
 * HttpPost - http post request
 * @param string   url  Http request url
 * @param array   arr  Post data array: var data = []; data['id'] = 1; data['cmd'] = 'get-user';
 * @param function cb   Callback function with response parametr
 * @param form obj   form Form object $('#form')[0];
 *
 * hr.setRequestHeader("Content-Type", "application/json");
 * data:
 * var json = {"email": "hey@mail.xx", "password": "1010101010"}
 * var data = JSON.stringify(json)
 *
 * hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 * data:
 * "fname=Henry&lname=Maxiu"
 */
function HttpPost(url, arr, cb, form){
    if (form === undefined) { var data = new FormData(); }else{ var data = new FormData(form); }
    if (arr !== undefined) {
        for (const index in arr) {
            data.append(index, arr[index]);
        }
    }
    var hr = new XMLHttpRequest();
    hr.onreadystatechange=function(){
        if (hr.readyState==4 && hr.status==200){
            if( typeof cb === 'function' ){ cb(hr.responseText); }
        }
    }
    hr.upload.onprogress = function(e) {
        var done = e.position || e.loaded, total = e.totalSize || e.total;
        console.log('xhr.upload progress: ' + done + ' / ' + total + ' = ' + (Math.floor(done/total*1000)/10) + '%');
    };
    hr.open("POST",url,true);
    hr.send(data);
}

// callback function
function callback(res){
   // Data from server
   console.log(res); //Show console: F12 or ctrl+shift+k
   var json = JSON.parse(res);
   for(i in json.list){
      console.log("Name from list : " + json.list[i].name);
   }
}

// New array
var arr = [];
arr['cmd'] = "doit";
arr['name'] = "Hellonix";

// Form with id
var form = $('#formID')[0];

// Send POST request, upload data and files from form to server
HttpPost("api.php",arr,callback,form);


// html form with base64image string or file
/*
<form method="POST" enctype="multipart/form-data" id="formID">
    <input type="text" name="username">
    <input type="text" name="filebase64" value="data:image/png;base64,...">
    <input type="file" name="file">
    <!-- multiple files -->
    <input type="file" name="file[]">
    <input type="submit" name="send" value="Send">
</form>
*/
