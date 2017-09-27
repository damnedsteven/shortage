<!doctype html public "-//w3c//dtd xhtml 1.0 transitional//en" "http://www.w3.org/tr/xhtml1/dtd/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="content-type" content="text/html; charset=gb2312" /> 
<title>超简单的php文件上传程序</title> 
</head> 
 
<body> 
<form id="form1" name="form1" enctype="multipart/form-data" method="post" action=""> 
  <label for="filefield"></label> 
  <input type="file" name="name" id="filefield" /> 
  <input type="submit" name="button" id="button" value="开始上传文件" /> 
</form> 
</body> 
</html> 
 
 
<? 
//文件上传 
 
 代码如下 复制代码  
if($_files ) 
{ 
 upfiles($_files,'./'); 
} 
function upfiles($files,$path){ 
 global $nowtimestamp; 
 $exname=strtolower(substr($files['name'],(strrpos($files['name'],'.')+1))); 
 $i=1; 
   if (!move_uploaded_file($files['tmp_name'], $path.$nowtimestamp.".".$exname)) {  
  showmessage("上传文件失败，请稍后重试！","?job=add",true); 
 } 
 return  $path.$nowtimestamp.".".$exname; 
} 
?> 