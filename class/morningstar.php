<?php
/**
 * Description of morningstar
 * The class name must
 * @author nhuvt Mar-21-2016
 */

require 'simple_html_dom.php';

class morningstar extends Scraper{

  function execute($param){
    $param['url'] = sprintf($param['url'], $param['id']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $param['url']);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $str_response = curl_exec($ch);
    !empty($str_response) or $this->outputApiError(curl_error($ch), $param);

    //Close curl resource to free up system resources
    curl_close($ch);

    //Output data
    $this->outputApiData($this->format($str_response), $param);
  }

  /*
  * Preformat returned data before convert it to array.
  */
  private function format($str_response){
    $result                 = array();
    $html                   = str_get_html($str_response);
    $price_info             = $html->find('div#overviewQuickstatsDiv', 0);
    preg_match_all("/[\w,]+/", $price_info->find('td.text', 0)->plaintext, $matches);
    list($result['currency'], $result['price']) = $matches[0];
    $result['price']        = utils::convertAnyStringToNumber($result['price']);
    $result['date']         = utils::convertAnyDateFormat($price_info->find('span.heading', 0)->plaintext, 'd/m/Y', 'Y-m-d');
    return array($result);
  }
}