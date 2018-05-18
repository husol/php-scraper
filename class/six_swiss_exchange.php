<?php

class six_swiss_exchange extends Scraper{

	function execute($param){
		$postVal = "id=".$param['id'].'&domain=107';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//to deal with gzip
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");

		curl_setopt($ch,CURLOPT_POST, 2);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $postVal);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

		//get data from url
		$dataCsv = curl_exec($ch);

		//Close curl resource to free up system resources
		curl_close($ch);

		$data = str_getcsv($dataCsv, "\n");
		foreach($data as &$row) $row = str_getcsv($row, ";");

		if(empty($data)){
		    $this->outputApiError($jsonString, $param);
		}

		// //Output data
		$this->outputApiData($this->format($data), $param);
	}

	private function format($data){
		//Remove first 3 elements
		array_shift($data); array_shift($data); array_shift($data);

		return array_map(function($row){
			return array(
				'date'		=>date('Y-m-d', strtotime($row[0])),
				'price'		=>$row[1],
				'currency'	=>'',
			);
		}, $data);
	}
}
