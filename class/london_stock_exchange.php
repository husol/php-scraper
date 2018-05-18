<?php

class london_stock_exchange extends Scraper{

	function execute($param){
		$time_frame = isset($param['time_frame']) ? $param['time_frame'] : "6m";
		$postVal = '{"request":{"SampleTime":"1d","TimeFrame":"' . $time_frame . '","RequestedDataSetType":"ohlc","ChartPriceType":"price","OffSet":-60,"FromDate":null,"ToDate":null,"UseDelay":true,"KeyType":"Topic","KeyType2":"Topic","Language":"en","Key":"'.$param['key'].'"}}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch,CURLOPT_POSTFIELDS, $postVal);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));

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
		$data = array_values($data['d']);

		return array_map(function($row){
			return array(
				'date'		=>date('Y-m-d', $row[0]/1000),
				'price'		=>$row[1],
				'currency'	=>'',
			);
		}, $data);
	}
}
