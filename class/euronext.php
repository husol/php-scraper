<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class euronext extends Scraper{

	function execute($param){
		//Compute current time and from time
		$currentTime = strtotime("now");
		$fromTime = strtotime("-1 months");

		$param['url'] = sprintf($param['url'], $fromTime, $currentTime, $param['isin']);

		$jsonString = file_get_contents($param['url']);
		$data = json_decode($jsonString, true);
		$data = $data['data'];

		if(empty($data)){
		    $this->outputApiError("Empty api result", $param);
		}

		//Output data
		$this->outputApiData($this->format($data), $param);
	}

	private function format($data){
        //Return an array.
		return array_map(function($row){
			return array(
				'date'		=>$row['date'],
				'price'		=>$row['close'],
				'currency'	=>$row['currency'],
			);
		}, $data);
	}
}
