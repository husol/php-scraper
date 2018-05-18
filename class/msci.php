<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of msci
 * The class name must
 * @author khoa
 */
class msci extends Scraper{

	function execute($param){
		$endDate = urlencode(date('d M, Y'));
		$startDate = urlencode(date('d M, Y', strtotime("-1 months")));

		$param['url'] = sprintf(urldecode($param['url']), $param['currency'], $param['indices'], $endDate, $startDate);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch); // execute curl request

		!empty($data) or $this->outputApiError(curl_error($ch), $param);

		//Close curl resource to free up system resources
		curl_close($ch);

		$dataArr = json_decode(json_encode((array) simplexml_load_string($data)), 1);

		// //Output data
		$this->outputApiData($this->format($dataArr['index']['asOf']), $param);
	}

	private function format($data){
		return array_map(function($obj){
			return array(
				'date'		=>date('Y-m-d', strtotime($obj['date'])),
				'price'		=>$obj['value'],
				'currency'	=> '',
			);
		}, $data);
	}
}
