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
class bloomberg_1 extends Scraper{

	function execute($param){
		$param['url'] = sprintf($param['url'], $param['Securities']);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//return the transfer as a string
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Accept: application/json, text/javascript, */*; q=0.01',
					'Accept-Language: en-US,en;q=0.5',
					'Connection: keep-alive',
					'Host: ' . $param['host'],
					'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:35.0) Gecko/20100101 Firefox/35.0',
					'X-Requested-With: XMLHttpRequest'
			));
		$jsonString = curl_exec($ch);
		!empty($jsonString) or $this->outputApiError(curl_error($ch), $param);

		//Close curl resource to free up system resources
		curl_close($ch);

		$data = array_filter(explode("\n", $jsonString));

		//For this site, the returned result is csv with header and footer.
		//we need to remove first(header) and last(footer) element
		if(count($data)<2 && !empty($jsonString)){
			$this->outputApiError($jsonString, $param);
		}
		array_shift($data);
		array_pop($data);

		//Output data
		$this->outputApiData($this->format($data), $param);
	}

	private function format($data){
		rsort($data);
		return array_map(function($val){
			list($unitDate, $price) = explode('"', $val);
			return array(
				'date'		=>date('Y-m-d', strtotime($unitDate)),
				'price'		=>$price,
				'currency'	=>''
			);
		}, $data);
	}
}
