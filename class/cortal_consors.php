<?php
/**
 * Description of dividend
 * The class name must be the filename
 * @author nhuvt
 */
require 'simple_html_dom.php';

class cortal_consors extends Scraper{

  function execute($param){
    $param['url'] = sprintf($param['url'], $param['isin']);
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

    $html_string = curl_exec($ch);
    !empty($html_string) or $this->outputApiError(curl_error($ch), $param);

    //Close curl resource to free up system resources
    curl_close($ch);

    $data = $this->formatReturnedData($html_string);
    if(empty($data)){
        $this->outputApiError("Empty api result", $param);
    }

    if(isset($data['error_code']) && !empty($data['error_desc'])){
      $this->outputApiError($data['error_desc'], $param);
    }

    //Output data
    $this->outputApiData($data, $param);
  }

  /*
  * Preformat returned data before convert it to array.
  */
  private function formatReturnedData($data){
    $result   = array();
    $html     = str_get_html($data);
    $currency = $html->find('div.ccui-r b', 0)->plaintext;
    $table    = $html->find('table.table-ew', 0);
    foreach($table->find('tr') as $tr){
      $date     = utils::convertAnyDateFormat($tr->find('td.date', 0)->plaintext, 'd/m/Y', 'Y-m-d');
      $price    = utils::convertAnyStringToNumber($tr->find('span', 0)->plaintext);
      $result[] = array(
        'date'      => $date,
        'price'     => $price,
        'currency'  => $currency,
        );
    }
    array_shift($result);
    array_shift($result);
    return $result;
  }
}