<?php

namespace Qlife;

//$device = new \Qlife\Document\Device\ANDBloodPressureMonitor();

require __DIR__ . '/../../modules/Qlife/Application.php';
\Qlife\Application::init();

$t = new \Qlife\TwoNetAPI();
$dm = \Qlife\Service\DocumentManager::getInstance();

$users = $dm->getRepository('Qlife\Document\User')->findAll();

foreach ($users as $user) {

  echo $user->getName(), "\n";

  $devices = $user->getDevices();

  foreach ($devices as $device) {
    if ($device->getTwoNetDeviceType() == "andbpm") {
      $device->setSortIndex(10);
    } else if ($device->getTwoNetDeviceType() == "andws") {
      $device->setSortIndex(20);
    } else if ($device->getTwoNetDeviceType() == "entra") {
      $device->setSortIndex(30);
    } else if ($device->getTwoNetDeviceType() == "nonin") {
      $device->setSortIndex(40);
    } else if ($device->getTwoNetDeviceType() == "withings") {
      $device->setSortIndex(50);
    }

    echo "  ", $device->getName(), ":", $device->getSortIndex(), "\n";
  }
}

$dm->flush();