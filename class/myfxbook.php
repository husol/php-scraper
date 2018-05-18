<?php

require 'simple_html_dom.php';

class myfxbook extends Scraper{

	function execute($param){
		//i.e: http://www.myfxbook.com/getHistoricalDataByDate.json?&start=2015-02-09&end=2015-07-10&symbol=USDHKD&timeScale=1440&userTimeFormat=0

		$endDate = date('Y-m-d');
		$startDate = date('Y-m-d', strtotime("$endDate -6 month"));

		$param['url'] = sprintf($param['url'], $startDate, $endDate, $param['symbol']);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//to deal with gzip
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");

		//get data from url
		$jsonStr = curl_exec($ch);

		//Close curl resource to free up system resources
		curl_close($ch);

		$objData = json_decode($jsonStr);
		$data = $objData->content->historyData;

		$data = $this->formatReturnedData($data);
		if(empty($data)){
		    $this->outputApiError("Empty api result", $param);
		}

		if(isset($data['error_code']) && !empty($data['error_desc'])){
			$this->outputApiError($data['error_desc'], $param);
		}

		//Output data
		$this->outputApiData($this->format($data), $param);
	}

	private function format($data){
        //Return an array.
		return array_map(function($row){
			return array(
				'date'	=>date('Y-m-d',strtotime($row['date'])),
				'price'	=>$row['price'],
				'currency'=>$row['currency'],
			);
		}, $data);
	}

	/*
	* Preformat returned data before convert it to array.
	*/
	private function formatReturnedData($data){
		$result = array();
		$html = str_get_html($data);
		$table = $html->find('table', 0);
		foreach($table->find('tr') as $tr){
			$result[] = array(
				'date'		=> substr($tr->find('td', 0)->plaintext, 0, 12),
				'price'		=> trim($tr->find('td', 4)->plaintext),
				'currency'	=> '',
				); 
		} 
		//remove the header
		array_shift($result);
	    return $result;
	}
}