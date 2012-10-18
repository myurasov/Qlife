<?php

namespace Qlife;

require __DIR__ . '/../../modules/Qlife/Application.php';
Application::init();

//

//$p = new \Qlife\Document\HealthProfile();
//$user = new \Qlife\Document\User();

//

$dm = \Qlife\Service\DocumentManager::getInstance();

$users = $dm->getRepository('Qlife\Document\User')->findAll();

foreach ($users as $user) {
  $p = new \Qlife\Document\HealthProfile();
  $dm->persist($p);

  $user->setHealthProfile($p);
  $p->setUser($user);

  $p->update();
  echo $p->getHealthScore(), "\n";
}

$dm->flush();