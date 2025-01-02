<?

function returnMarkedDates($fromDate,$toDate=''){	
	if($toDate == '') {
		$dyear = date('Y', strtotime($fromDate));
		$dmonth = date('m', strtotime($fromDate));
		$ddate = cal_days_in_month(CAL_GREGORIAN, $dmonth, $dyear);
		$toDate = $dyear.'-'.$dmonth.'-'.$ddate;
		$toDate = date('Y-m-d', strtotime($toDate.' +7 days'));
	}

	if($fromDate)
		$fromDate = date('Y-m-d', strtotime($fromDate.' -7 days'));

	$datesArray = [];
	global $_CURRENT_USER;
	

	

           

	foreach(explode(',', $_CURRENT_USER->active_site()) as $ssid) {
		
		$que = "SELECT year.date, IFNULL(b.packDiscount, a.packDiscount) AS `discount`
			FROM `year` INNER JOIN `sites_weekly_hours` AS `a` ON (a.holidayID = 0 AND a.siteID = " . $ssid . " AND a.weekday = year.weekday)
				LEFT JOIN `sites_periods` AS `sp` ON (sp.siteID = a.siteID AND sp.periodType = 0 AND year.date BETWEEN sp.dateFrom AND sp.dateTo)
				LEFT JOIN `sites_weekly_hours` AS `b` ON (b.siteID = a.siteID AND b.holidayID = -sp.periodID AND b.weekday = a.weekday AND b.active = 1)
			WHERE year.date BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
			HAVING `discount` > 0
			ORDER BY year.date";
		
		//echo $que;
		$resData[$ssid] = udb::single_column($que);


		
	}
	//print_r($resData);
	//print_r($datesArray);
	return $resData;
	
}


function whatsappBuild($phone,$body){
	$phone = preg_replace('/[^0-9]/', '', $phone);
	if(strlen($phone)>11 && substr($phone,0,1)!="0"){

	}else{
		$phone ="972".ltrim($phone, '0');
	}
	if(1==1){
		echo "https://api.whatsapp.com/send/?phone=".$phone."&text=".$body;
	}else{
		echo "///wa.me/".$phone."?text=".$body;
	}

}

?>