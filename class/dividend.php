<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dividend
 * The class name must
 * @author ddminh May-5-2015
 */
class dividend extends Scraper{

	function execute($param){
		//i.e: http://www.dividend.com/dividend-stocks/technology/personal-computers/aapl-apple-inc/payouthistory.json/
		$param['url'] = sprintf($param['url'], $param['p1'], $param['p2'], $param['p3']);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//to deal with gzip
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Accept: application/json, text/javascript, */*; q=0.01',
					'Accept-Language: en-US,en;q=0.5',
					'Accept-Encoding: gzip, deflate',
					'Accept-Charset:UTF-8,*;q=0.5rn',
					'Referer: '. $param['url'], 
					'Connection: keep-alive',
					'Host: ' . $param['host'],
					'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:35.0) Gecko/20100101 Firefox/35.0',
					'X-Requested-With: XMLHttpRequest'
			));
		$jsonString = curl_exec($ch);
		!empty($jsonString) or $this->outputApiError(curl_error($ch), $param);

		//Close curl resource to free up system resources
		curl_close($ch);

		$fullData = json_decode($jsonString, true);
		$data = $this->formatReturnedData($fullData);

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
			$parts = $row['parts'];
			return array(
				'payable_date'	=>date('Y-m-d',strtotime($parts['Pay Date'])),
				'amount'		=>$row['y'],
				'ex_date'		=>date('Y-m-d',strtotime($parts['Ex Date'])),
			);
		}, $data);
	}

	/*
	* Preformat returned data before convert it to array.
	*/
	private function formatReturnedData($data){
		if(!empty($data['series'][0])){
			return $data['series'][0]['data'];
		}
	    return false;
	}
}