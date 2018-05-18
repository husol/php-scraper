<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of scraper
 *
 * @author ddminh
 */
class Scraper {

    protected $allConfig;
    private $siteParam;

    function __construct() {
        $this->allConfig = parse_ini_file('site.ini', true);
    }

    function scraperIt() {
        $this->getSiteConfig();
        $siteObj = new $this->siteParam['class']();
        $siteObj->execute($this->siteParam);
    }

    function outputApiError($message, $info) {
        $data = utils::getSample($info);
        $data->error = $message;
        $data->lastrunstatus = false;
        $this->outputJson($data);
        die();
    }

    function outputApiData($data, $info) {
        if (empty($data)) {
            return $this->outputApiError("No data found", $info);
        }
        $dataObj = utils::getSample($info);
        $dataObj->lastrunstatus = true;
        $dataObj->results->collection1 = $data;
        $this->outputJson($dataObj);
    }

    protected function outputJson($data) {
        //Ouput the file
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');

        //need a pretty print?
        $isPretty = utils::getVar('pretty');
        if (isset($isPretty)) {
            echo utils::prettyPrint(json_encode($data));
            return;
        }
        echo json_encode($data);
    }

    protected function getSiteConfig() {
        //Check if the site is defined
        $site = utils::getVar('site');
        if (!isset($this->allConfig[$site])) {
            die('invalid site:' . htmlspecialchars($site));
        }
        $conf = $this->allConfig[$site];
        //Check mandatory param:
        if (isset($conf['require'])) {
            foreach ($conf['require'] as $varName) {
                $var = utils::getVar($varName);
                if (empty($var) && $var != 0) {
                    die("Missing $varName");
                }
                $conf[$varName] = $var;
            }
        }
        //Check optional param:
        if (isset($conf['optional'])) {
            foreach ($conf['optional'] as $varName) {
                $var = utils::getVar($varName);
                $conf[$varName] = $var;
            }
        }
        $this->siteParam = $conf;
        return true;
    }

}
