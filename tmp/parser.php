<?php

date_default_timezone_set('Asia/Shanghai');
ini_set('max_execution_time', 12000); //12000 seconds = 200 minutes
ini_set('pcre.backtrack_limit', '10485760'); // 10mb limit

if (true) {
	$url = 'http://shopfloor-apj.sfng.int.hpe.com/sfweb/OpenOrdersReport?operations_m=none&fromBirthStamp='.date("Y-m-d",strtotime(date("Y-m-d H:i")."-1 days")).'+'.date("H",strtotime(date("Y-m-d H:i")."-1 days")).'%3A'.date("i",strtotime(date("Y-m-d H:i")."-1 days")).'&toBirthStamp='.date("Y-m-d").'+'.date("H").'%3A'.date("i").'&coaStatus=All&shipPoint=BF40&&queryType=openOrders&sortBy=Sales+Order';
	
	// $url = 'http://shopfloor-apj.sfng.int.hpe.com/sfweb/AllOrdersReport?operations_m=none&fromBirthStamp='.date("Y-m-d",strtotime(date("Y-m-d H:i")."-1 days")).'+'.date("H",strtotime(date("Y-m-d H:i")."-1 days")).'%3A'.date("i",strtotime(date("Y-m-d H:i")."-1 days")).'&toBirthStamp='.date("Y-m-d").'+'.date("H").'%3A'.date("i").'&coaStatus=All&shipPoint=BF40&&queryType=allOrders&sortBy=Sales+Order';
	
	// $url = 'http://shopfloor-apj.sfng.int.hpe.com/sfweb/OpenOrdersReport?operations_m=none&fromBirthStamp=2016-12-12+10%3A55&toBirthStamp=2016-12-12+17%3A55&coaStatus=All&shipPoint=BF40&&queryType=openOrders&sortBy=Sales+Order';
	
	// Create a stream
	$opts = array(
	  'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n" .
				  "Cookie: timezoneOffset=480\r\n"
	  )
	);

	$context = stream_context_create($opts);

	// Open the file using the HTTP headers set above
	$info = file_get_contents($url, false, $context);
	
	if (isset($info)) {
		preg_match("/<table(.*)<\/table>/isU",$info,$content);//??????,????????

		if (!empty($content)) {
			preg_match_all("/<tr(.*)<\/tr>/isU",$content[0],$rows); // ??????,?????
			
			if (!empty($rows)) {
				$n = 0;
				
				foreach ($rows[0] as $row) {
					preg_match_all("/<td(.*)<\/td>/isU",$row,$cols); // ?????,?????
					
					if (!empty($cols[0])) {
						$order[$n]['Product Family'] = trim(strip_tags($cols[0][0]));
						$order[$n]['SO#'] = trim(strip_tags($cols[0][1]));
						$order[$n]['OSP#'] = trim(strip_tags($cols[0][2]));
						$order[$n]['PLO#'] = trim(strip_tags($cols[0][3]));
						$order[$n]['Line#'] = trim(strip_tags($cols[0][4]));
						$order[$n]['Part#'] = trim(strip_tags($cols[0][6]));
						$order[$n]['Part Desc'] = trim(strip_tags($cols[0][7]));
						$order[$n]['Qty'] = trim(strip_tags($cols[0][8]));
						$order[$n]['Shipref'] = trim(strip_tags($cols[0][22]));
						$order[$n]['Product Line'] = trim(strip_tags($cols[0][23]));
						// ???, ?????? arr ???
						$timestamp = strtotime(trim(strip_tags($cols[0][25])) . "-30 minutes");
						$order[$n]['RTP Date'] = date('Y-m-d H:i:s', $timestamp);
						
						$n++;
					} 	
				}

			} else {
				echo '?????';
				$order = null;
			}
		} else {
			echo '?????';
			$order = null;
		}
	} else {
		echo 'SFNG_Default Page Not Found';
		
		$order = null;
	}
	// var_dump($order);
}

?>