<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife;

class Utils {

  /**
   * Get price in dollars & cents from ##.## - formatted string
   */
  public static function getPriceUSCentsFromString($string) {
    $m = array();

    if (preg_match('/^([0-9\,]*)(?:\.([0-9]{1,2}))?$/', $string, $m)) {
      $dollars = $m[1];
      $dollars = str_replace(',', '', $dollars);
      $dollars = intval($dollars);
      $cents = isset($m[2]) ? intval($m[2]) : 0;

      return $dollars * 100 + $cents;
    } else {
      throw new \Exception('Wrong price format');
    }
  }
}