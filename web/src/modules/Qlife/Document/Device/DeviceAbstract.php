<?php

/**
 * Device document
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document\Device;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Qlife\TwoNetAPI;
use Qlife\Config;
use Qlife\Document\DocumentAbstract;

/**
 * @ODM\Document(collection="devices")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="deviceType")
 * @ODM\DiscriminatorMap({
 *  1="EntraGlucometer",
 *  2="NoninPulseOximeter",
 *  3="ANDWeightScale",
 *  4="ANDBloodPressureMonitor",
 *  5="WithingsBloodPressureMonitor",
 * })
 */
abstract class DeviceAbstract extends DocumentAbstract {

  // auth types
  const AUTH_CREDENTIALS = 0;
  const AUTH_OAUTH = 1;

  // health status
  const HEALTH_UNKNOWN = -1;
  const HEALTH_OK = 0;
  const HEALTH_WARNING = 1;
  const HEALTH_CRITICAL = 2;

  /** @ODM\Id */
  protected $id;

  /**
   * @ODM\Date
   * @var \DateTime
   */
  protected $createdAt;

  /** @ODM\String */
  protected $name;

  /** @ODM\String */
  protected $serialNumber;

  /** @ODM\String */
  private $trackGuid; // 2net device guid

  /** @ODM\String */
  protected $twoNetDeviceType = ''; // 2net device type

  /** @ODM\String */
  protected $twoNetMeasureType = ''; // 2net device type

  /** @ODM\Int */
  protected $authType = self::AUTH_CREDENTIALS; // authentification type

  /** @ODM\String */
  protected $deviceClass;

  /** @ODM\Int */
  protected $health = self::HEALTH_UNKNOWN; // diagnosed health status

  /** @ODM\Collection */
  protected $diagnosticMessages = array();

  /** @ODM\Int */
  protected $sortIndex;

  /**
   * @ODM\ReferenceOne(
   *  targetDocument="\Qlife\Document\User"
   * )
   * @var \Qlife\Document\User
   */
  protected $user;

  /** @ODM\Boolean */
  protected $isPrivate = false;

  /** @ODM\Collection */
  protected $recentData;

  //

  public function __construct() {
    $this->createdAt = new \DateTime();
  }

  /**
   * Register with 2net api
   */
  public function register($credentials = null) {

    // default credentioals include SN only
    if (is_null($credentials)) {
      $credentials = array('serialNumber' => $this->serialNumber);
    }

    if ($this->authType == self::AUTH_CREDENTIALS) {

      // look for guid in cache
      if (!is_null($this->user->getCachedTrackGuids())) {
        $res = $this->user->getCachedTrackGuids();

        if (isset($res['trackGuidsResponse'][$this->twoNetDeviceType . 'TrackGuids'])) {
          $this->trackGuid = $res
              ['trackGuidsResponse']
              [$this->twoNetDeviceType . 'TrackGuids']
              ['guid'];

          return true; // guid found in cache
        }
      }

      $twoNetApi = new TwoNetAPI();
      $registerUrl = '/partner/register/' . $this->twoNetDeviceType;

      $res = $twoNetApi->request('POST', $registerUrl, array(
        'registerRequest' => array(
          'guid' => $this->user->getGuid(),
          'credentials' => $credentials
      )));

      if (isset($res['trackGuidsResponse'])) { // save guid

        $this->trackGuid = $res
            ['trackGuidsResponse']
            [$this->twoNetDeviceType . 'TrackGuids']
            ['guid'];

        return true;

      } else if (isset($res['errorStatus']) && $res['errorStatus']['code'] == 90004) { // already registered

        // get guid
        $res = $twoNetApi->request('GET', "/partner/user/tracks/" . $this->user->getGuid());

        $this->trackGuid = $res
            ['trackGuidsResponse']
            [$this->twoNetDeviceType . 'TrackGuids']
            ['guid'];

        return true;
      }

      return false;
    }
  }

  /**
   * Fetch recent data
   */
  public function fetchRecentData() {

    $twoNet = new TwoNetAPI();

    $res = $twoNet->request("POST", "/partner/measure/{$this->twoNetMeasureType}/filtered/", array(
      'measureRequest' => array(
        'guid' => $this->user->getGuid(),
        'trackGuid' => $this->trackGuid,
        'filter' => array(
          'startDate' => time() - Config::$options['recentDataPeriod'], // 1 week,
          'endDate' => time() + /* to avoid TZ/clock diff/, add a week */ 60 * 60 * 24 * 7
    ))));

    if (isset($res['measureResponse'])) {
      if ($res['measureResponse']['status']['code'] == 1) { // ok

        $measures = $res['measureResponse']['measures']['measure'];

        if (isset($measures[0])) {
          $this->recentData = $measures;
        } else {
          $this->recentData = array($measures); // single measure
        }

        // sort
        $this->sortRecentData();
      }
    }
  }

  /**
   * Sort recent data by time, ASC
   */
  public function sortRecentData() {
    if (is_array($this->recentData) && count($this->recentData)) {
      usort($this->recentData, function ($a, $b) {
          return $a['time'] - $b['time'];
      });
    }
  }

  /**
   * Diagnose user health based on recent data
   */
   public function diagnose() {
     $this->health = self::HEALTH_UNKNOWN;

     $this->diagnosticMessages = array();

     if (!is_array($this->recentData) || count($this->recentData) == 0) {
       $this->diagnosticMessages[] = array(0, "Not enough data for diagnostics yet");
       return false;
     }

     return true;
  }

  /**
   * Add two measures
   */
  abstract public function addMeasures($a, $b);

  /**
   * Divide measure
   */
  abstract public function divideMeasure($measure, $factor);

  /**
   * Calculate latest measure from few recent ones
   */
  public function getRecentAvgMeasure($num = 6) {
    $c = count($this->recentData);

    // try top get avg of recent measures
    $num = min(count($this->recentData), $num);

    if ($c) {
      $latest = $this->recentData[$c - 1];

      // sumamrize
      for ($i = 1; $i < $num; $i++) {
        $latest = $this->addMeasures($latest, $this->recentData[$c - $i - 1]);
      }

      $latest = $this->divideMeasure($latest, $num);

      return $latest;
    }


    return null;
  }

  // <editor-fold defaultstate="collapsed" desc="Accessors">

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getCreatedAt() {
    return $this->createdAt;
  }

  public function setCreatedAt($createdAt) {
    $this->createdAt = $createdAt;
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getSerialNumber() {
    return $this->serialNumber;
  }

  public function setSerialNumber($serialNumber) {
    $this->serialNumber = $serialNumber;
  }

  public function getUser() {
    return $this->user;
  }

  public function setUser($user) {
    $this->user = $user;
  }

  public function getTrackGuid() {
    return $this->trackGuid;
  }

  public function setTrackGuid($trackGuid) {
    $this->trackGuid = $trackGuid;
  }

  public function getTwoNetDeviceType() {
    return $this->twoNetDeviceType;
  }

  public function setTwoNetDeviceType($twoNetDeviceType) {
    $this->twoNetDeviceType = $twoNetDeviceType;
  }

  public function getTwoNetMeasureType() {
    return $this->twoNetMeasureType;
  }

  public function setTwoNetMeasureType($twoNetMeasureType) {
    $this->twoNetMeasureType = $twoNetMeasureType;
  }

  public function getAuthType() {
    return $this->authType;
  }

  public function setAuthType($authType) {
    $this->authType = $authType;
  }

  public function getRecentData() {
    return $this->recentData;
  }

  public function setRecentData($recentData) {
    $this->recentData = $recentData;
  }

  public function getDeviceClass() {
    return $this->deviceClass;
  }

  public function setDeviceClass($deviceClass) {
    $this->deviceClass = $deviceClass;
  }

  public function getHealth() {
    return $this->health;
  }

  public function setHealth($health) {
    $this->health = $health;
  }

  public function getDiagnosticMessages() {
    return $this->diagnosticMessages;
  }

  public function setDiagnosticMessages($diagnosticMessages) {
    $this->diagnosticMessages = $diagnosticMessages;
  }

  public function getSortIndex() {
    return $this->sortIndex;
  }

  public function setSortIndex($sortIndex) {
    $this->sortIndex = $sortIndex;
  }

  public function getIsPrivate() {
    return $this->isPrivate;
  }

  public function setIsPrivate($isPrivate) {
    $this->isPrivate = $isPrivate;
  }

  // </editor-fold>
}
