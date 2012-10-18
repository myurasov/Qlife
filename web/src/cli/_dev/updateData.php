<?php

require __DIR__ . '/../../modules/Qlife/Application.php';
\Qlife\Application::init();

$t = new \Qlife\TwoNetAPI();
$dm = \Qlife\Service\DocumentManager::getInstance();

$users = $dm->getRepository('Qlife\Document\User')->findAll();

foreach ($users as $user) {
  echo $user->getName(), "\n";
  $devices = $user->getDevices();

  foreach ($devices as $device) {
    echo "  ", $device->getName(), "\n";
    $device->fetchRecentData();
    $device->diagnose();
  }
}

$dm->flush();