<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class EntraGlucometer extends DeviceAbstract {
  /** @ODM\String */
  protected $twoNetDeviceType = 'entra';

  /** @ODM\String */
  protected $twoNetMeasureType = 'blood';

  /** @ODM\String */
  protected $deviceClass = 'entra';

  public function addMeasures($a, $b) {
    $a['blood']['glucose'] += $b['blood']['glucose'];
    return $a;
  }

  public function divideMeasure($a, $factor) {
    $a['blood']['glucose'] /= $factor;
    return $a;
  }

  public function diagnose() {
    if (parent::diagnose()) {
      $recentAvg = $this->getRecentAvgMeasure(48);
      $healthRisk = 0;

      // http://en.wikipedia.org/wiki/Blood_sugar#Normal_values_in_humans

      if ($recentAvg['blood']['glucose'] < 50) {
        $this->diagnosticMessages[] = array(3, 'Glucose level is extremely low!');
        $healthRisk += 3;
      } else if ($recentAvg['blood']['glucose'] < 82) {
        $this->diagnosticMessages[] = array(1, 'Glucose level is slightly below normal');
        $healthRisk += 1;
      } else if ($recentAvg['blood']['glucose'] < 110) {
        $this->diagnosticMessages[] = array(0, 'Glucose level is excellent!');
        $healthRisk += 0;
      } else if ($recentAvg['blood']['glucose'] < 125) {
        $this->diagnosticMessages[] = array(2, 'Glucose level is above normal');
        $healthRisk += 2;
      } else {
        $this->diagnosticMessages[] = array(3, 'Glucose level is extremely high!');
        $healthRisk += 3;
      }

      if ($healthRisk <= 1) {
        $this->health = self::HEALTH_OK;
      } else if ($healthRisk <= 2) {
        $this->health = self::HEALTH_WARNING;
      } else {
        $this->health = self::HEALTH_CRITICAL;
      }
    }
  }
}