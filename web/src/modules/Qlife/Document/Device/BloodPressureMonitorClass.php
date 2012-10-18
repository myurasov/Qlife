<?php

/**
 * BloodPressureMonitorClass
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Qlife\Document\User;

/**
 * @ODM\Document
 */
abstract class BloodPressureMonitorClass extends DeviceAbstract {
  /** @ODM\String */
  protected $deviceClass = 'bpm';

  /** @ODM\String */
  protected $twoNetMeasureType = 'blood';

  public function addMeasures($a, $b) {
    $a['blood']['pulse'] += $b['blood']['pulse'];
    $a['blood']['systolic'] += $b['blood']['systolic'];
    $a['blood']['diastolic'] += $b['blood']['diastolic'];
    return $a;
  }

  public function divideMeasure($a, $factor) {
    $a['blood']['pulse'] /= $factor;
    $a['blood']['systolic'] /= $factor;
    $a['blood']['diastolic'] /= $factor;
    return $a;
  }

  public function diagnose() {
    if (parent::diagnose()) {
      $recentAvg = $this->getRecentAvgMeasure(24);
      $healthRisk = 0;

      if ($this->user->getGender() == User::GENDER_MALE) {
        $pulseLevels = array(65, 80, 90);
      } else {
        $pulseLevels = array(75, 90, 100);
      }

      // blood pressure

      if ($recentAvg['blood']['systolic'] <= 125 && $recentAvg['blood']['diastolic'] <= 80) {
        $this->diagnosticMessages[] = array(0,'Blood pressure is excellent!');
      } else if ($recentAvg['blood']['systolic'] <= 135 || $recentAvg['blood']['diastolic'] <= 90) {
        $this->diagnosticMessages[] = array(1, 'Blood pressure is slighly above normal');
        $healthRisk += 1;
      } else if ($recentAvg['blood']['systolic'] <= 160 || $recentAvg['blood']['diastolic'] <= 100) {
        $this->diagnosticMessages[] = array(3,'There is a probability of Stage 1 Hypertension');
        $healthRisk += 3;
      } else if ($recentAvg['blood']['systolic'] > 160 || $recentAvg['blood']['diastolic'] > 100) {
        $this->diagnosticMessages[] = array(5,'There is a probability of Stage 2 Hypertension');
        $healthRisk += 5;
      }

      // pulse

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
      } else if ($healthRisk <= 5) {
        $this->health = self::HEALTH_WARNING;
      } else {
        $this->health = self::HEALTH_CRITICAL;
      }
    }
  }
}