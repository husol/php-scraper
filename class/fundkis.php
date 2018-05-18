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
class fundkis extends Scraper{

	function execute($param){
		$param['url'] = sprintf($param['url'], $param['hashcode']);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//to deal with gzip
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Accept: application/json, text/javascript, */*; q=0.01',
					'Accept-Language: en-US,en;q=0.5',
					'Accept-Charset:UTF-8,*;q=0.5rn', 
					'Connection: keep-alive',
					'Host: ' . $param['host'],
					'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:35.0) Gecko/20100101 Firefox/35.0',
					'X-Requested-With: XMLHttpRequest'
			));
		$jsonString = curl_exec($ch);
		!empty($jsonString) or $this->outputApiError(curl_error($ch), $param);

		//Close curl resource to free up system resources
		curl_close($ch);

		$data = json_decode($jsonString, true);
		if(empty($data)){
		    $this->outputApiError($jsonString, $param);
		}

		// //Output data
		$this->outputApiData($this->format($data), $param);
	}

	private function format($data){
		return array_map(function($obj){
			return array(
				'date'		=>date('Y-m-d', strtotime($obj['NavDate'])),
				'price'		=>$obj['Nav'],
				'currency'	=>$obj['NavCurrencyISO'],
			);
		}, $data);
	}
}
