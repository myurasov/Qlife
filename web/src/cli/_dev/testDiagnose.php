<?php

require __DIR__ . '/../../modules/Qlife/Application.php';
\Qlife\Application::init();

$t = new \Qlife\TwoNetAPI();
$dm = \Qlife\Service\DocumentManager::getInstance();

$users = $dm->getRepository('Qlife\Document\User')->findAll();

foreach ($users as $user) {
  $devices = $user->getDevices();

  foreach ($devices as $device) {
    echo $device->getName(), "\n";
    $device->diagnose();

    print_r($device->getDiagnosticMessages());
    var_dump($device->getHealth());
  }
}

$dm->flush();