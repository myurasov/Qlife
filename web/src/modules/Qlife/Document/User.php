<?php

/**
 * User document
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;
use Qlife\Service\DocumentManager;
use Qlife\TwoNetAPI;
use Qlife\Document\Device\DeviceAbstract;
/**
 * @ODM\Document(collection="users")
 */
class User extends DocumentAbstract {

  const GENDER_UNKNOWN = 0;
  const GENDER_MALE = 1;
  const GENDER_FEMALE = 2;

  /** @ODM\Id */
  protected $id; // also used as guid for 2net

  /** @ODM\Boolean */
  protected $isAdmin = false;

  /** @ODM\Date */
  protected $createdAt;

  /** @ODM\String */
  protected $name;

  /** @ODM\String */
  protected $firstName;

  /** @ODM\String */
  protected $lastName;

  /** @ODM\String */
  protected $email;

  /**
   * @ODM\String
   * @ODM\UniqueIndex
   */
  protected $readableId;

  /** @ODM\String */
  protected $facebookAccessToken;

  /** @ODM\String */
  protected $facebookId;

  /** @ODM\Date */
  protected $birthday;

  /** @ODM\Int */
  protected $gender = self::GENDER_MALE;

  /** @ODM\Float */
  protected $height; // [cm]

  /**
   * @ODM\ReferenceMany(
   *  targetDocument="\Qlife\Document\Device\DeviceAbstract"
   * )
   */
  protected $devices;

  /**
   * @ODM\EmbedOne(targetDocument="HealthProfile")
   * @var HealthProfile
   */
  protected $healthProfile;

  //

  // cached track guids response
  private $cachedTrackGuids;

  //

  public function __construct() {
    $this->createdAt = new \DateTime();
    $this->devices = new ArrayCollection();
  }

  // get user guid for 2net api
  public function getGuid() {
    return 'fb-' . $this->facebookId;
  }

  public function addDevice(DeviceAbstract $device) {
    $this->devices->add($device);
  }

  public function removeDevice(DeviceAbstract $device)
  {
    $this->devices->removeElement($device);
    DocumentManager::getInstance()->remove($device);
  }

  /**
   * Create readable id
   * Uses js procedure executed by MongoDB
   * Should be called immideately before saving to database
   */
  public function createReadableId() {
    $firstName = json_encode($this->firstName);
    $lastName = json_encode($this->lastName);

    $js = file_get_contents(\mym\PATH_RESOURCES . '/User.createReadableId.js');

    $js = str_replace(array(
      "'%firstName%'",
      "'%lastName%'",
      ), array(
        $firstName,
        $lastName
      ), $js);

    $mongo = DocumentManager::getMongoDB();
    $result = $mongo->execute($js);

    if ($result['ok']) {
      $this->readableId = $result['retval'];
    }
    else {
      throw new \Exception("Unable to generate readable id");
    }
  }

  /**
   * Register with 2net app
   */
  public function registerWithTwoNet() {
    $twoNet = new TwoNetAPI();
    $this->cachedTrackGuids = $twoNet->request("POST", "/partner/register",
      array('registerRequest' => array(
        'guid' => $this->getGuid()
    )));
  }

  /**
   * Add demo devices
   */
  public function addDemoDevices() {
    $dm = DocumentManager::getInstance();

    // A&D Blood Pressure Monitor (demo)

    $device = new Device\ANDBloodPressureMonitor();
    $device->setSerialNumber('2NET00004');
    $device->setName('A&D Blood Pressure Monitor');
    $device->setUser($this);
    $device->register();
    $device->fetchRecentData();
    $device->diagnose();
    //
    $dm->persist($device);
    $this->addDevice($device);

    // Entra Glucometer (demo)

    $device = new Device\EntraGlucometer();
    $device->setSerialNumber('2NET00001');
    $device->setName('Entra Glucometer');
    $device->setUser($this);
    $device->register();
    $device->fetchRecentData();
    $device->diagnose();
    //
    $dm->persist($device);
    $this->addDevice($device);

    // Nonin PulseOximter (demo)

    $device = new Device\NoninPulseOximeter();
    $device->setSerialNumber('2NET00002');
    $device->setName('Nonin PulseOximter');
    $device->setUser($this);
    $device->register();
    $device->fetchRecentData();
    $device->diagnose();
    //
    $dm->persist($device);
    $this->addDevice($device);

    // A&D Weight scale (demo)

    $device = new Device\ANDWeightScale();
    $device->setSerialNumber('2NET00003');
    $device->setName('A&D Weight Scale');
    $device->setUser($this);
    $device->register();
    $device->fetchRecentData();
    $device->diagnose();
    //
    $dm->persist($device);
    $this->addDevice($device);

    $dm->flush();
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

  public function setCreatedAt(\DateTime $createdAt) {
    $this->createdAt = $createdAt;
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getFirstName() {
    return $this->firstName;
  }

  public function setFirstName($firstName) {
    $this->firstName = $firstName;
  }

  public function getLastName() {
    return $this->lastName;
  }

  public function setLastName($lastName) {
    $this->lastName = $lastName;
  }

  public function getFacebookAccessToken() {
    return $this->facebookAccessToken;
  }

  public function setFacebookAccessToken($facebookAccessToken) {
    $this->facebookAccessToken = $facebookAccessToken;
  }

  public function getFacebookId() {
    return $this->facebookId;
  }

  public function setFacebookId($facebookId) {
    $this->facebookId = $facebookId;
  }

  public function getReadableId() {
    return $this->readableId;
  }

  public function setReadableId($readableId) {
    $this->readableId = $readableId;
  }

  public function getBirthday() {
    return $this->birthday;
  }

  public function setBirthday($birthday) {
    $this->birthday = $birthday;
  }

  public function getDevices() {
    return $this->devices;
  }

  public function setDevices(ArrayCollection $devices) {
    $this->devices = $devices;
  }

  public function getCachedTrackGuids() {
    return $this->cachedTrackGuids;
  }

  public function getIsAdmin() {
    return $this->isAdmin;
  }

  public function setIsAdmin($isAdmin) {
    $this->isAdmin = $isAdmin;
  }

  public function getGender() {
    return $this->gender;
  }

  public function setGender($gender) {
    $this->gender = $gender;
  }

  public function getHeight() {
    return $this->height;
  }

  public function setHeight($height) {
    $this->height = $height;
  }

  public function getEmail() {
    return $this->email;
  }

  public function setEmail($email) {
    $this->email = $email;
  }

  public function getHealthProfile() {
    return $this->healthProfile;
  }

  public function setHealthProfile($healthProfile) {
    $this->healthProfile = $healthProfile;
  }

  // </editor-fold>
}
