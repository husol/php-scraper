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
require 'simple_html_dom.php';

class tmxmoney_dividend extends Scraper{

	function execute($param){
		//i.e: http://api.tmxmoney.com/quote/api.js?symbol=BBD.B:TSX&lang=en
		$param['url'] = sprintf($param['url'], $param['symbol']);
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

		$data = $this->formatReturnedData($jsonString);
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
				'payable_date'	=>date('Y-m-d',strtotime($row['pay_date'])),
				'amount'		=>$row['amount'],
				'ex_date'		=>date('Y-m-d',strtotime($row['ex_date'])),
			);
		}, $data);
	}

	/*
	* Preformat returned data before convert it to array.
	*/
	private function formatReturnedData($data){
		$result = array();
		$html = str_get_html($data);
		$table = $html->find('table', 1);
		foreach($table->find('tr') as $tr){
			$result[] = array(
				'ex_date'    => $tr->find('td', 0)->plaintext,
				'pay_date'   => $tr->find('td', 3)->plaintext,
				'amount'     => $tr->find('td', 4)->plaintext,
				); 
		} 
		//remove the header
		array_shift($result);
	    return $result;
	}
}