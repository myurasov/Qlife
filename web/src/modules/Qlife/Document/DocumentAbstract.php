<?php

/**
 * Document abstarct
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document;

abstract class DocumentAbstract {

  /**
   * Get document as array
   *
   * @return array
   */
  public function toArray($params = array()) {
    if (!is_array($params)) {
      $params = func_get_args();
    }

    if (count($params) == 0) {
      $res = get_object_vars($this);
    }
    else { // arguments is a list of properties
      $res = array();

      for ($i = 0; $i < count($params); $i++) {
        if (property_exists($this, $params[$i])) {
          $res[$params[$i]] = $this->$params[$i];
        }
      }
    }

    // remove non-scalar values
    foreach ($res as $k => $v) {
      if (!is_scalar($v) && !in_array($k, $params)) {
        unset($res[$k]);
      }
    }


    return $res;
  }

  /**
   * Get object as JSON
   *
   * @return string
   */
  public function toJson($params = array()) {
    if (!is_array($params)) {
      $params = func_get_args();
    }

    return json_encode($this->toArray($params));
  }
}