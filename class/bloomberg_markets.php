<?php

/**
 * Description of bloomberg_markets
 * The class name must
 * @author nhuvt
 */
class bloomberg_markets extends Scraper{

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

    $data = json_decode($jsonString, true);

    //Output data
    $this->outputApiData($this->format($data[0]['price']), $param);
  }

  private function format($data){
    return array_map(function($obj){
      return array(
        'date'      =>date('Y-m-d', strtotime($obj['date'])),
        'price'     =>$obj['value'],
        'currency'  => '',
      );
    }, $data);
  }
}
