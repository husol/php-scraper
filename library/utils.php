<?php

class utils {

    public static function getVar($name) {
        return filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
    }

    /**
     * Sample Result:
      {
      "name": "bloomberg_chart_CMR_747 9exi99ia",
      "count": 22,
      "frequency": "On demand",
      "version": 10686,
      "newdata": true,
      "lastrunstatus": "success",
      "lastsuccess": "Thu Feb 05 2015 15:30:04 GMT+0000 (UTC)",
      "thisversionstatus": "success",
      "thisversionrun": "Thu Feb 05 2015 15:30:02 GMT+0000 (UTC)",
      "results": {
      "collection1": [
      {
      "date": "20150106",
      "price": "2627.02",
      "currency": "EUR"
      },
      ]
      }
      }
     * @param type $data
     * @return type
     */
    public static function getSample($info) {
        $dataObj = new stdClass();
        $dataObj->collection1 = array();

        $resultObj = new stdClass();
        $resultObj->class = $info['class'];
        $resultObj->lastrunstatus = true;
        $resultObj->site = $info['host'];
        $resultObj->url = $info['url'];
        $resultObj->error = '';
        $resultObj->results = $dataObj;
        return $resultObj;
    }

    /*
      Use this function as the version of php is 5.3.3
      On php 5.4 we should use JSON_PRETTY_PRINT
     */

    public static function prettyPrint($json) {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen($json);

        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if ($ends_line_level !== NULL) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ($in_escape) {
                $in_escape = false;
            } else if ($char === '"') {
                $in_quotes = !$in_quotes;
            } else if (!$in_quotes) {
                switch ($char) {
                    case '}': case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{': case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } else if ($char === '\\') {
                $in_escape = true;
            }
            if ($new_line_level !== NULL) {
                $result .= "\n" . str_repeat("\t", $new_line_level);
            }
            $result .= $char . $post;
        }

        return $result;
    }

    /*
     * Convert a string (of number) to mysql number
     * i.e:
     * +------------+-----------+
     * |   String   |  Number   |
     * +------------+-----------+
     * | 9,461.00   | 9461      |
     * | 1 234,56   | 1234.56   |
     * | 900.461,01 | 900461.01 |
     * | 234.56     | 234.56    |
     * | $234,56    | 234.56    |
     * | 1,000.00   | 1000      |
     * +------------+-----------+
     * Created by Minh Aug-21-2014 issue CMR-637
     */

    public static function convertAnyStringToNumber($string) { //convertAnyStringToNumber
        //remove all character except number, dot and commas character.
        $string = preg_replace('/[^\d.,-]+/', '', $string);
        $lastOfDot = strrpos($string, ".");
        $lastOfCommas = strrpos($string, ",");

        if ($lastOfDot === false && $lastOfCommas === false) {
            //123456 >> 123456
            return floatval($string);
        } else if ($lastOfDot >= 0 && $lastOfCommas === false) {
            //123.456 >> 123.456
            return floatval($string);
        } else if ($lastOfCommas >= 0 && $lastOfDot === false) {
            //123,456 >> 123.456
            return floatval(str_replace(",", ".", $string));
        } else if ($lastOfCommas > $lastOfDot) {
            //2.345,23 >> 2345.23
            return floatval(str_replace(",", ".", str_replace(".", "", $string)));
        } else if ($lastOfDot > $lastOfCommas) {
            //234,234.93 >> 234234.93
            return floatval(str_replace(",", "", $string));
        }
    }

    public static function convertAnyDateFormat($str_date, $from_format, $to_format) {
        $date = DateTime::createFromFormat($from_format, trim($str_date));

        return $date->format($to_format);
    }
}
