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
			$refreshQuery = "UPDATE master SET status = 0";
			$db->query($refreshQuery);
            //parse data from csv file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
                //check whether shortage record for plo already exists in database
                $prevQuery = "SELECT plo FROM master WHERE plo = '".$line[10]."' AND pn = '".$line[11]."'";
                $prevResult = $db->query($prevQuery);
				//time formatting
				$line[0] = date('Y-m-d H:i:s',strtotime($line[0]));
				$line[3] = date('Y-m-d H:i:s',strtotime($line[3]));
				$line[4] = date('Y-m-d H:i:s',strtotime($line[4]));
                if($prevResult->num_rows > 0){
                    //update plo data
                    $db->query("UPDATE master SET pfc = '".$line[1]."', orderday = '".$line[2]."', bkpl = '".$line[3]."', rtp = '".$line[4]."', so = '".$line[5]."', so_item = '".$line[6]."', product = '".$line[7]."', product_pl = '".$line[8]."', bpo = '".$line[9]."', ctrl_id = '".$line[12]."', sales_area = '".$line[13]."', shortage_qty = '".$line[14]."', required_qty = '".$line[15]."', lastupdated = NOW(), status = 1 WHERE plo = '".$line[10]."' AND pn = '".$line[11]."'");
                }else{
                    //insert plo data into database
                    $db->query("INSERT INTO master (publish, pfc, orderday, bkpl, rtp, so, so_item, product, product_pl, bpo, plo, pn, ctrl_id, sales_area, shortage_qty, required_qty, import_time, status) VALUES ('".$line[0]."','".$line[1]."','".$line[2]."','".$line[3]."','".$line[4]."','".$line[5]."','".$line[6]."','".$line[7]."','".$line[8]."','".$line[9]."','".$line[10]."','".$line[11]."','".$line[12]."','".$line[13]."','".$line[14]."','".$line[15]."', NOW(), 1)");
                }
            }
            
            //close opened csv file
            fclose($csvFile);
			
			//refresh all records and set their status to inactive
			$refreshQuery = "	UPDATE pn SET status = 0, lastupdated = now();
								UPDATE pn SET status = 1, lastupdated = now()
								WHERE 
								pn IN (SELECT pn FROM master WHERE status = 1);
			";
			$db->query($refreshQuery);
			
			$db->query("
					INSERT INTO pn (pn, ctrl_id, buyer_name, shortage_qty, pline_shortage_qty, passthru_shortage_qty, earliest_bkpl, lastupdated, status, is_copy)
					SELECT
						*,
						now(),
						1,
						0
					FROM
					(SELECT 
						pn, 
						ctrl_id,
						name,
						SUM(shortage_qty) shortage_qty,
						SUM(CASE WHEN pfc <> 'PassThrough' THEN shortage_qty ELSE 0 END) pline_shortage_qty,
						SUM(CASE WHEN pfc = 'PassThrough' THEN shortage_qty ELSE 0 END) passthru_shortage_qty,
						MIN(bkpl) earliest_bkpl
					FROM 
						master m
						LEFT JOIN
						(select id, name from buyer) b
						ON m.ctrl_id=b.id
					WHERE
						status = 1
					GROUP BY
						pn,
						ctrl_id,
						name) AS t
					ON DUPLICATE KEY UPDATE 
						shortage_qty=t.shortage_qty, pline_shortage_qty=t.pline_shortage_qty, passthru_shortage_qty=t.passthru_shortage_qty, earliest_bkpl=t.earliest_bkpl,
						arrival_qty = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE arrival_qty END,
						eta = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE eta END,
						slot = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE slot END,
						remark = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE remark END,
						carrier = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE carrier END,
						judge_supply = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE judge_supply END,
						is_overdue = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE is_overdue END,
						shortage_reason = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE shortage_reason END,
						shortage_reason_detail = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE shortage_reason_detail END,
						bill_number = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE bill_number END,
						delivery = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE delivery END,
						delay_reason = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE delay_reason END,
						vehicle_info = CASE WHEN received IS NOT NULL OR status=0 THEN NULL ELSE vehicle_info END,
						received = NULL END;
			");
			
			// $db->query("
					// UPDATE pn SET status = 1, lastupdated = now()
					// WHERE 
					// pn IN (SELECT pn FROM (SELECT * FROM pn) AS p WHERE status = 1) 
					// AND 
					// received IS NULL;
			// ");

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