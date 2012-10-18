<?php

/**
 * HealthProfile
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Qlife\Document\Device\DeviceAbstract;

/**
 * @ODM\EmbeddedDocument
 */
class HealthProfile extends DocumentAbstract {

  /** @ODM\Id */
  protected $id;

  /** @ODM\Float */
  protected $healthScore;

  /** @ODM\Hash */
  protected $blood = array();

  /** @ODM\Hash */
  protected $body = array();

  /**
   * @ODM\ReferenceOne(
   *  targetDocument="\Qlife\Document\User"
   * )
   * @var \Qlife\Document\User
   */
  protected $user;

  /**
   * Update
   */
  public function update() {
    $device = new Device\ANDBloodPressureMonitor;

    if (count($this->user->getDevices())) {

      // health score

      $badPointsMax = 0;
      $badPoints = 0;

      foreach ($this->user->getDevices() as $device) {
        $health = $device->getHealth();

        if ($health != DeviceAbstract::HEALTH_UNKNOWN) {
          $badPointsMax += DeviceAbstract::HEALTH_CRITICAL;
          $badPoints += $health;
        }
      }

      $badScore = 0;

      if ($badPointsMax > 0) {
        $badScore = $badPoints /  $badPointsMax;
      }

      $healthScore = 1 - $badScore;
      $healthScore = pow($healthScore, 1/2);

      $this->healthScore = $healthScore * 100;

      // data

      foreach ($this->user->getDevices() as $device) {
        $measure = $device->getRecentAvgMeasure(1);

        if ($device->getDeviceClass() == 'bpm') {
          $this->blood['pulse'] = $measure['blood']['pulse'];
          $this->blood['systolic'] = $measure['blood']['systolic'];
          $this->blood['diastolic'] = $measure['blood']['diastolic'];
        } else if ($device->getDeviceClass() == 'andws') {
          $this->body['weight'] = $measure['body']['weight'];
        } else if ($device->getDeviceClass() == 'entra') {
          $this->blood['glucose'] = $measure['blood']['glucose'];
        } else if ($device->getDeviceClass() == 'nonin') {
          $this->blood['pulse'] = $measure['blood']['pulse'];
          $this->blood['spo2'] = $measure['blood']['spo2'];
        }
      }
    }
  }

  // <editor-fold defaultstate="collapsed" desc="Accessors">

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getHealthScore() {
    return $this->healthScore;
  }

  public function setHealthScore($healthScore) {
    $this->healthScore = $healthScore;
  }

  public function getBlood() {
    return $this->blood;
  }

  public function setBlood($blood) {
    $this->blood = $blood;
  }

  public function getBody() {
    return $this->body;
  }

  public function setBody($body) {
    $this->body = $body;
  }

  public function getUser() {
    return $this->user;
  }

  public function setUser($user) {
    $this->user = $user;
  }

  // </editor-fold>
}
