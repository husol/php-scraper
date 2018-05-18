<?php
/**
 * Description of dividend
 * The class name must
 * @author ddminh May-5-2015
 */
//curl --compressed 'http://www.boerse-frankfurt.de/en/parts/boxes/company_data_dividend_inner.m' -H 'Accept: */*' -H 'Accept-Encoding: gzip, deflate' -H 'Accept-Language: en-US,en;q=0.5' -H 'Cache-Control: no-cache' -H 'Connection: keep-alive' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -H 'Cookie: _ga=GA1.2.797235098.1410494037; __gads=ID=4df772f163f15464:T=1431314229:S=ALNI_MatQR1gQS7u87Pdu4pNoEZR8hRIQA; POPUPCHECK=1431400629976; _gat=1' -H 'Host: www.boerse-frankfurt.de' -H 'Pragma: no-cache' -H 'Referer: http://www.boerse-frankfurt.de/en/equities/adidas+ag+DE000A1EWWW0/company+data' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:37.0) Gecko/20100101 Firefox/37.0' -H 'X-Requested-With: XMLHttpRequest' --data 'pages_total=2&COMPONENT_ID=PREKOP77892a8bbb523c34a37943180ff330f1528_dividend&include_url=%2Fparts%2Fboxes%2Fcompany_data_dividend_inner.m&item_count=15&items_per_page=10&title=&ag=291&secu=291&page_size=100&page=0'
require 'simple_html_dom.php';

class frankfurt_dividend extends Scraper{

	function execute($param){
		$cdata = sprintf($param['cdata'], $param['id'], $param['id']);
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
					'X-Requested-With: XMLHttpRequest',
			));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $cdata);

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
			$apiDate = date('Y-m-d',strtotime( str_replace('/', '-', $row['pay_date'])));
			return array(
				'payable_date'	=> $apiDate,
				'ex_date'		=> $apiDate,
				'amount'		=> utils::convertAnyStringToNumber($row['amount']),
			);
		}, $data);
	}

	/*
	* Preformat returned data before convert it to array.
	*/
	private function formatReturnedData($data){
		$result = array();
		$html = str_get_html($data);
		$table = $html->find('table', 0);
		foreach($table->find('tr') as $tr){
			$result[] = array(
				'ex_date'    => $tr->find('td', 0)->plaintext, //same with pay_date
				'pay_date'   => $tr->find('td', 0)->plaintext,
				'amount'     => $tr->find('td', 2)->plaintext,
				); 
		} 
		//remove the header
		array_shift($result);
	    return $result;
	}
}