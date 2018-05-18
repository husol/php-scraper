<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of frankfurt
 * The class name must be the filename
 * @author khoa
 */
class frankfurt extends Scraper{

	function execute($param){
		$param['url'] = sprintf($param['url'], $param['instruments']);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$jsonString = curl_exec($ch);
		!empty($jsonString) or $this->outputApiError(curl_error($ch), $param);

		//Close curl resource to free up system resources
		curl_close($ch);

		$data = json_decode($jsonString, true);

		if(empty($data)){
		    $this->outputApiError("Empty api result", $param);
		}

		//CMR-1367: (2) Reduce data and Remove no price
        $finalData = array();
        foreach($data['instruments'][0]['data'] as $k => $item) {
            if ($item[2] == '') {
                continue;
            }
            $finalData[] = $item;
        }

		//Output data
		$this->outputApiData($this->format($finalData), $param);
	}

	private function format($data){
		//Return an array.
		return array_map(function($row){
            return array(
                'date'		=> date('Y-m-d', $row[0]/1000),
                'price'		=> $row[2],
                'currency'	=> ''
            );
		}, $data);
	}

}
