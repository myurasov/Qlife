<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Qlife\Document\User;

/**
 * @ODM\Document
 */
class NoninPulseOximeter extends DeviceAbstract {
  /** @ODM\String */
  protected $twoNetDeviceType = 'nonin';

  /** @ODM\String */
  protected $twoNetMeasureType = 'blood';

  /** @ODM\String */
  protected $deviceClass = 'nonin';

  public function addMeasures($a, $b) {
    $a['blood']['pulse'] += $b['blood']['pulse'];
    $a['blood']['spo2'] += $b['blood']['spo2'];
    return $a;
  }

  public function divideMeasure($a, $factor) {
    $a['blood']['pulse'] /= $factor;
    $a['blood']['spo2'] /= $factor;
    return $a;
  }

  public function diagnose() {
    if (parent::diagnose()) {
      $recentAvg = $this->getRecentAvgMeasure(100);
      $healthRisk = 0;

      // spo2

      if ($recentAvg['blood']['spo2'] < 90) {
        $this->diagnosticMessages[] = array(3, 'Oxygele level is extremely low!');
        $healthRisk += 3;
      } else if ($recentAvg['blood']['spo2'] < 95) {
        $this->diagnosticMessages[] = array(1, 'Oxygele level is slightly below normal');
        $healthRisk += 1;
      } else {
        $this->diagnosticMessages[] = array(0, 'Oxygele level is excellent!');
        $healthRisk += 0;
      }

      // pulse

      if ($this->user->getGender() == User::GENDER_MALE) {
        $pulseLevels = array(65, 80, 90);
      } else {
        $pulseLevels = array(75, 90, 100);
      }

      $recentAvg = $this->getRecentAvgMeasure(24);

      if ($recentAvg['blood']['pulse'] < $pulseLevels[0]) {
        $this->diagnosticMessages[] = array(0, 'Heart rate is excellent!');
      } else if ($recentAvg['blood']['pulse'] < $pulseLevels[1]) {
        $this->diagnosticMessages[] = array(1, 'Heart rate is a little bit above normal');
        $healthRisk += 1;
      } else if ($recentAvg['blood']['pulse'] < $pulseLevels[2]) {
        $this->diagnosticMessages[] = array(2, 'Heart rate is above normal');
        $healthRisk += 2;
      } else if ($recentAvg['blood']['pulse'] >= $pulseLevels[2]) {
        $this->diagnosticMessages[] = array(3, 'Heart rate is extremely high');
        $healthRisk += 3;
      }

      if ($healthRisk <= 2) {
        $this->health = self::HEALTH_OK;
      } else if ($healthRisk <= 4) {
        $this->health = self::HEALTH_WARNING;
      } else {
        $this->health = self::HEALTH_CRITICAL;
      }
    }
  }
}