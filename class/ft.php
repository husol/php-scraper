<?php

/**
 * Description of ft
 * The class name must
 * @author khoaht 01-Nov-2016
 */
require 'simple_html_dom.php';

class ft extends Scraper {

    function execute($param) {
        $param['url'] = sprintf($param['url'], $param['type'], $param['symbol']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $param['url']);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $str_response = curl_exec($ch);
        !empty($str_response) or $this->outputApiError(curl_error($ch), $param);

        //Close curl resource to free up system resources
        curl_close($ch);

        $strArr = explode('data-mod-results-inceptiondate=', $str_response);
        if(stripos($strArr[0], 'data-mod-results-startdate=')) {
            preg_match('/data-mod-results-startdate="(.*?)"/', $strArr[0], $match);
            $startDate = $match[1]+31;
        }

        $url = "http://markets.ft.com/data/equities/ajax/getmorehistoricalprices?resultsStartDate={$startDate}&symbol={$param['symbol']}&isLastRowStriped=false";
        $json = file_get_contents($url);
        $json = json_decode($json);
        
        //Output data
        $this->outputApiData($this->format($json), $param);
    }

    /*
     * Preformat returned data before convert it to array.
     */

    private function format($str_json) {
        $html = str_get_html(htmlspecialchars_decode($str_json->data->html));
        $infoText = $html->find('tr');
        unset($html);
        $result = array();
        foreach ($infoText as $info) {
            $result[] = array('date' => date('Y-m-d', strtotime($info->children(0)->children(1)->plaintext)),
                            'price' => utils::convertAnyStringToNumber($info->children(4)->plaintext),
                            'currency' => 11
                        );
        }
        return $result;
    }

}
