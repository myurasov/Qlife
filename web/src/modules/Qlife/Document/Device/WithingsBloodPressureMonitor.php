<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class WithingsBloodPressureMonitor extends BloodPressureMonitorClass {
  /** @ODM\String */
  protected $twoNetDeviceType = 'withings';
}