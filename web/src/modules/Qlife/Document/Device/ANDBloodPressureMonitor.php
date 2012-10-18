<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class ANDBloodPressureMonitor extends BloodPressureMonitorClass {
  /** @ODM\String */
  protected $twoNetDeviceType = 'andbpm';
}