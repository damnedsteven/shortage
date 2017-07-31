<?php
//load the database configuration file
include 'dbConfig.php';

if(isset($_POST['importSubmit'])){
    
    //validate whether uploaded file is a csv file
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            
            //open uploaded csv file with read only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            
            //skip first two lines
            fgetcsv($csvFile);
			fgetcsv($csvFile);
            
			//refresh all records and set their status to inactive
			$refreshQuery = "UPDATE buyer SET status = 0";
			$db->query($refreshQuery);
			
            //parse data from csv file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
                //check whether record already exists in database
                $prevQuery = "SELECT * FROM buyer WHERE id = '".$line[0]."'";
                $prevResult = $db->query($prevQuery);

                if($prevResult->num_rows > 0){
                    //update data
                    $db->query("UPDATE buyer SET name = '".$line[1]."', status = 1 WHERE id = '".$line[0]."'");
                }else{
                    //insert data into database
                    $db->query("INSERT INTO buyer (id, name) VALUES ('".$line[0]."','".$line[1]."')");
                }
            }
			
			//delete inactive
			$cleanQuery = "DELETE FROM buyer WHERE status = 0";
			$db->query($cleanQuery);
            
            //close opened csv file
            fclose($csvFile);

            $qstring = '?status=succ';
        }else{
            $qstring = '?status=err';
        }
    }else{
        $qstring = '?status=invalid_file';
    }
}

//redirect to the listing page
header("Location: index.php".$query);