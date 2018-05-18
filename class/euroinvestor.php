<?php
/**
 * Description of dividend
 * The class name must be the filename
 * @author nhuvt
 */

class euroinvestor extends Scraper{

  function execute($param){
    $param['url']   = sprintf($param['url'], $param['instrumentId']);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $param['url']);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //to deal with gzip
    curl_setopt($ch,CURLOPT_ENCODING , "gzip");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

    //get data from url
    $dataCsv = curl_exec($ch);

    //Close curl resource to free up system resources
    curl_close($ch);

    $data = str_getcsv($dataCsv, "\n");

    foreach($data as &$row) $row = str_getcsv($row, ",");

    if(empty($data)){
        $this->outputApiError($dataCsv, $param);
    }

    // //Output data
    $this->outputApiData($this->format($data), $param);
  }

  private function format($data){
    //Remove first 3 elements
    array_shift($data);

    return array_map(function($row){
      $date = utils::convertAnyDateFormat($row[0], 'd/m/Y H:i:s', 'Y-m-d');
      return array(
        'date'      => $date,
        'price'     => $row[4],
        'currency'  => '',
      );
    }, $data);
  }
}
