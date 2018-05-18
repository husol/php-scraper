<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class yahoo_dividend extends Scraper{

	function execute($param){
		//format current date and assign tu url
		$currDate = date('\&\d=n\&\e=d\&\f=Y');
		$param['url'] = sprintf($param['url'], $param['s'], $currDate);
		//get data from url
		$dataCsv = file_get_contents($param['url']);
		$data = array_map("str_getcsv", explode("\n", $dataCsv));

		if(empty($data)){
		    $this->outputApiError("Empty api result", $param);
		}

		//Output data
		$this->outputApiData($this->format($data), $param);
	}

	private function format($data){
		//Remove first and last elements
		array_shift($data);	array_pop($data);
        //Return an array.
		return array_map(function($row){
			return array(
				'payable_date'	=>date('Y-m-d',strtotime($row[0])),
				'amount'		=>$row[1],
				'ex_date'		=>date('Y-m-d',strtotime($row[0])),
			);
		}, $data);
	}
}
