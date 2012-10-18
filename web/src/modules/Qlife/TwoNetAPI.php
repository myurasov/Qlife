<?php

/**
 * 2net API helper
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife;

use Exception;
use Qlife\Config;

class TwoNetAPI {

  private $auth;
  private $endpointUrl = "https://twonetcom.qualcomm.com/kernel";

  /**
   * Constructor
   */
  public function __construct() {
    // b64(key:secret)
    $this->auth = base64_encode(
      Config::$options['TwoNet']['key'] . ':' .
      Config::$options['TwoNet']['secret']
    );
  }

  /**
   * Create request xml body from array
   */
  private function createRequestXml($data) {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
      $this->_createRequestXml($data);
  }

  /**
   * Create XML from array
   */
  private function _createRequestXml($data) {
    $r = "";

    if (is_array($data)) {
      foreach ($data as $k => $v) {
        $r .= "<$k>" .
          $this->_createRequestXml($v) .
          "</$k>";
      }
    } else {
      $r = "$data";
    }

    return rtrim($r);
  }

  /**
   * Make request
   */
  public function request($method = "GET", $path = "", $data = '') {

    $method = strtoupper($method);

    // create xml body
    if (is_array($data)) {
      $data = $this->createRequestXml($data);
    }

    // configure curl

    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_HTTPHEADER => array(
        "Authorization: Basic " . $this->auth,
        // "Accept-Encoding: gzip",
        "Content-Type: application/xml",
        "Accept: application/json"
        ),
      CURLOPT_URL => $this->endpointUrl . $path,
      CURLOPT_POST => $method == 'GET' ? false : true,
      CURLOPT_RETURNTRANSFER => true,
      // CURLOPT_VERBOSE => true
    ));

    if ($method != "GET") {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    // make request
    $res = curl_exec($ch);

    // curl error
    if ($res === false) {
      throw new Exception("Transfer error: " . curl_error($ch));
    }

    // decode json
    $res = @json_decode($res, true);

    return $res;
  }
}