<?php
/**
 * Description of bondsupermart
 * The class name must
 * @author khoaht 27-Oct-2016
 */

require 'simple_html_dom.php';

class bondsupermart extends Scraper{

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
    $jsonStr = '';
    foreach (explode(';', $str_response) as $sentence) {
        if(stripos($sentence, 'bond_price_ONE_YEAR') && stripos($sentence, 'redrawPriceChartByJson')) {
            preg_match('/redrawPriceChartByJson\((.*?)\)/', $sentence, $match);
            $jsonStr = $match[1];
        }
    }

    if  (empty($jsonStr)) {
        return false;
    }
    $result = json_decode($jsonStr);
    $bidPrice = array();
    $askPrice = array();
    foreach ($result as $item) {
        if (trim($item->name) == 'Bid Price') {
            $bidPrice = $item->data;
        }
        if (trim($item->name) == 'Ask Price') {
            $askPrice = $item->data;
        }
    }

    $result = array();
    foreach ($bidPrice as $key => $priceItem) {
        $result[$priceItem[0]] = $priceItem[1];
    }
    $data = array();
    foreach ($askPrice as $key => $priceItem) {
        $obj = new stdClass();
        $obj->date = date('Y-m-d', $priceItem[0]/1000);
        $obj->price = ($result[$priceItem[0]] + $priceItem[1]) / 2;
        $obj->currency = 1;

        $data[] = $obj;
    }

    return $data;
  }
}