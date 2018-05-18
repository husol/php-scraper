<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of bloomberg_1
 * The class name must
 * @author ddminh
 */
class asx_dividend extends Scraper{

	function execute($param){
		//i.e: http://data.asx.com.au/data/1/company/BOQ/dividends?callback=angular.callbacks._2
		$param['url'] = sprintf($param['url'], $param['code']);
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
					// 'Referer: http://www.asx.com.au/asx/research/company.do', 
					'Connection: keep-alive',
					'Host: ' . $param['host'],
					'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:35.0) Gecko/20100101 Firefox/35.0',
					'X-Requested-With: XMLHttpRequest'
			));
		$jsonString = curl_exec($ch);
		!empty($jsonString) or $this->outputApiError(curl_error($ch), $param);

		//Close curl resource to free up system resources
		curl_close($ch);

		$jsonString = $this->formatReturnedString($jsonString);
		$data = json_decode($jsonString, true);
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
			list($payable_date, $payable_time ) = explode('T', $row['payable_date']);
			list($ex_date, $ex_time ) = explode('T', $row['ex_date']);
			return array(
				'payable_date'	=>date('Y-m-d',strtotime($payable_date)),
				'amount'		=>$row['amount'],
				'ex_date'		=>date('Y-m-d',strtotime($ex_date)),
			);
		}, $data);
	}

	/*
	* Pre format returned string before convert it to array.
	*/
	private function formatReturnedString($data){
        $matches = array();
        if(preg_match('/^angular\.callbacks\._2\((?P<json>.*)\);$/',$data, $matches)){
            return $matches['json'];
        }
	    return '';
	}
}