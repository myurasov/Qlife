<?php

/**
 * 'mym' namespace configuration options
 *
 * @copyright 2010-2012 Mikhail Yurasov
 * @package mym
 */

namespace mym;

class Config
{
  public static $options = array();

  public static function selfInit()
  {
    static::$options = array(
      // Project name
      'projectName' => \mym\PROJECT_NAME,

      // Project version
      'projectVersion' => \mym\PROJECT_VERSION,

      // HTTP router
      'httpRouter' => 'Qlife\Service\Router',

      // Paths to libraries
      'libraries' => array(
        'SymfonyComponents_HttpFoundation'
          => '/Projects/_libraries/php/Symfony_Components/HttpFoundation',
        'FirePHP'
          => '/Projects/_libraries/php/firephp-core',
      )
    );
  }
}

// Self-initialize
Config::selfInit();