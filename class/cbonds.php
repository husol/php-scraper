<?php

/**
 * Description of dividend
 * The class name must be the filename
 * @author nhuvt
 */
require 'simple_html_dom.php';

class cbonds extends Scraper {

    function execute($param) {
        $postDataString = sprintf('ground_title=Cbonds+Valuation&emission_id=%s&emission_country_id=%s&emission_kind_id=%s', $param['id'], $param['country_id'], $param['kind_id']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataString);

        //return the transfer as a string
        $jsonStr = curl_exec($ch);
        !empty($jsonStr) or $this->outputApiError(curl_error($ch), $param);

        //Close curl resource to free up system resources
        curl_close($ch);

        $data = $this->formatData($jsonStr);
        if (empty($data)) {
            $this->outputApiError("Empty api result", $param);
        }

        if (isset($data['error_code']) && !empty($data['error_desc'])) {
            $this->outputApiError($data['error_desc'], $param);
        }

        //Output data
        $this->outputApiData($data, $param);
    }

    /*
     * Preformat returned data before convert it to array.
     */

    private function formatData($jsonStr) {
        $result = json_decode($jsonStr);
        $bidPrice = array();
        $askPrice = array();

        foreach ($result as $item) {
            if (trim($item->name) == 'Bid') {
                $bidPrice = $item->data;
            }
            if (trim($item->name) == 'Ask') {
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
            $obj->currency = '';

            $data[] = $obj;
        }

        return $data;
    }

}
