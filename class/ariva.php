<?php
/**
 * Description of dividend
 * The class name must be the filename
 * @author nhuvt
 */

class ariva extends Scraper{

  function execute($param){
    $endDate = date('d.m.Y');
    $startDate = date('d.m.Y', strtotime('-6 months'));

    $param['url']   = sprintf($param['url'], $param['secu'], $param['id'], $param['payout'], $startDate, $endDate);

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

    foreach($data as $key => &$row) {
        if (empty(trim($row))) {
            unset($data[$key]);
            continue;
        }
        $row = str_getcsv($row, ";");
    }

    if(empty($data)){
        $this->outputApiError($dataCsv, $param);
    }

    // //Output data
    $this->outputApiData($this->format($data), $param);
  }

  private function format($data){
    //Remove first element
    array_shift($data);

    return array_map(function($row){
        if (is_null($row[0])) {
            return;
        }
        return array(
          'date'      => $row[0],
          'price'     => $row[4],
          'currency'  => 1,
        );
    }, $data);
  }
}
