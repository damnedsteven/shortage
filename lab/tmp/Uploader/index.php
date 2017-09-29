<form action="" enctype="multipart/form-data" method="post" name="upload">  
    选择文件:<input type="file" name="file" /><br /><br />  
             <input type="submit" value="上传文件"/>  
</form> 
  
<?php  
    header("Content-Type:text/html; charset=utf-8");  
    function count_size($bit)  
    {  
        $type = array('Bytes','KB','MB','GB','TB');  
        for($i = 0; $bit >= 1024; $i++)  
        {  
            $bit/=1024;  
        }  
        return (floor($bit*100)/100).$type[$i];  
    }//文件单位转换  
  
    $name = @$_FILES['file']['name'];  
    $type = @$_FILES['file']['type'];  
    $tmp_name = @$_FILES['file']['tmp_name'];  
    $size = @$_FILES['file']['size'];  
    $temp = count_size($size);  
  
    if($name)  
    {  
        echo '文件信息:'.'<br />';  
        echo '--------------------------------'.'<br />';  
        echo "文件名：".$name.'<br />';  
        echo '文件类型：'.$type.'<br />';  
        echo '临时文件名字:'.$tmp_name.'<br />';  
        echo '文件大小:'.$temp.'<br />';  
        $path = 'upload_file_test/';  
        echo '<br />'.'上传状态:'.'<br />';  
        echo '--------------------------------'.'<br />';  
        if(move_uploaded_file($tmp_name, $path.$name))  
            echo '文件上传成功！'.'<br />';  
        else  
            echo '文件上传失败！'.'<br />';  
    }  
?> 