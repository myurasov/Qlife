<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class ANDWeightScale extends DeviceAbstract {
  /** @ODM\String */
  protected $twoNetDeviceType = 'andws';

  /** @ODM\String */
  protected $twoNetMeasureType = 'body';

  /** @ODM\String */
  protected $deviceClass = 'andws';

  public function addMeasures($a, $b) {
    $a['body']['weight'] += $b['body']['weight'];
    return $a;
  }

  public function divideMeasure($a, $factor) {
    $a['body']['weight'] /= $factor;
    return $a;
  }
}