<?php

/**
 * Application controller
 *
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife;

use mym\Kernel;

class Application {

  public static function init() {
    // mym configuration
    define('mym\PROJECT_NAME', 'Qlife');
    define('mym\PROJECT_VERSION', '1.3');
    define('mym\PATH_ROOT', realpath(__DIR__ . '/../../..'));
    define('mym\RELOC_CONFIG', true);

    // id file
    require \mym\PATH_ROOT . '/instance.php';

    // mym
    require '/Projects/mym-dev/src/mym/Kernel.php';

    self::_registerAutoloading();

    // sessions
    if (PHP_SAPI != 'cli') {
      self::_setupSessions();
    }
  }

  /**
   * Register autoloading
   */
  private static function _registerAutoloading() {
    // Project
    Kernel::registerAutoloadNamespace(
      \mym\PROJECT_NAME, \mym\PATH_MODULES . '/' . \mym\PROJECT_NAME, true
    );

    // mym\Component
    Kernel::registerAutoloadNamespace(
      'mym\Component', \mym\PATH_MODULES . '/mym\Component'
    );

    // HttpFoundation
    Kernel::registerAutoloadNamespace(
      'Symfony\Component\HttpFoundation',
      \mym\Config::$options['libraries']['SymfonyComponents_HttpFoundation']
    );
  }

  /**
   * Setup session handling
   */
  private static function _setupSessions() {
    // sessions settings
    ini_set('session.name', Config::$options['sessionName']);
    ini_set('session.cookie_lifetime', Config::$options['sessionTimeout']);
    ini_set('session.gc_maxlifetime', Config::$options['sessionTimeout']);
    ini_set('session.cache_limiter', '');
  }
}