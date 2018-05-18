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
class europa extends Scraper{

	function execute($param){
		$lastOneMonth = date("Y-m-d", strtotime("-1 months"));
		//we only get data for one past month
		$param['url'] = sprintf($param['url'], $param['key'], $lastOneMonth);

		//Get the XML data via curl. Using curl to avoid some issue with file_get_content or some similar function.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $param['url']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		$xmlString = curl_exec($ch);
		!empty($xmlString) or $this->outputApiError(curl_error($ch), $param);
		curl_close($ch);
		
		// //Output data
		$this->outputApiData($this->format($xmlString), $param);
	}

	
	private function format($xml_string){
		// Friday 13th: 2dminh: i have an issue when parsing XML with a colons in tag names?
		// They suggested something on the internet http://stackoverflow.com/questions/1575788/php-library-for-parsing-xml-with-a-colons-in-tag-names
		// but nothing work for me. have to replace it.
		$xml = new SimpleXMLElement(str_replace(array("message:","generic:"), array("",""), $xml_string));
		$data = array();
		foreach($xml->xpath('//Obs') as $priceInfo){
			$dateObj =  $priceInfo->ObsDimension;
			$dateVal =  $dateObj['value'];

			$priceObj = $priceInfo->ObsValue;
			$priceVal = $priceObj['value'];

		    $data[] = array(
		   		'date'		=>(string)$dateVal[0],
				'price'		=>(string)$priceVal[0],
				'currency'	=>'',
		   	);
		}
		rsort($data);
		return $data;
	}
}
