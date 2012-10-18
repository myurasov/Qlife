<?php

/**
 * Front controller
 * @copyright 2012 Mikhail Yurasov
 */

require __DIR__ . '/../modules/Qlife/Application.php';
Qlife\Application::init();

mym\Kernel::handleHttpRequest();