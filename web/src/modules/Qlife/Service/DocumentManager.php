<?php

/**
 * Doctrine MongoDB ODM document manager creation
 *
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Service;

use Doctrine\Common\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentManager as DM;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Cache\ApcCache;
use Qlife\Config;

class DocumentManager {
  static private $instance = null;

  /**
   * Create DocumentManager instance
   */
  private static function _createInstance() {
    // ClassLoader
    require_once Config::$options['libraries']['Doctrine_Common'] .
      '/lib/Doctrine/Common/ClassLoader.php';

    // ODM
    $classLoader = new ClassLoader('Doctrine\ODM\MongoDB',
        Config::$options['libraries']['Doctrine_MongoDB_ODM'] . '/lib');
    $classLoader->register();

    // Common
    $classLoader = new ClassLoader('Doctrine\Common',
        Config::$options['libraries']['Doctrine_Common'] .
        '/lib');
    $classLoader->register();

    // MongoDB
    $classLoader = new ClassLoader('Doctrine\MongoDB',
        Config::$options['libraries']['Doctrine_MongoDB'] . '/lib');
    $classLoader->register();

    // Metadata driver
    $metadataDriver = new AnnotationDriver(new AnnotationReader(),
        \mym\PATH_MODULES . '\\' . \mym\PROJECT_NAME . '\Document');
    AnnotationDriver::registerAnnotationClasses();

    // Metadata cache
    $metadataCache = new ApcCache();

    // Connection
    $connection = new Connection(Config::$options['mongoServer']);

    // Configuration
    $config = new Configuration();
    $config->setProxyDir(\mym\PATH_TEMP . '/doctrine');
    $config->setProxyNamespace('Proxies');
    $config->setHydratorDir(\mym\PATH_TEMP . '/doctrine');
    $config->setHydratorNamespace('Hydrators');
    $config->setDefaultDB(\mym\PROJECT_NAME);
    $config->setAutoGenerateProxyClasses(true);
    $config->setAutoGenerateHydratorClasses(true);
    if (!\mym\DEVELOPMENT)
      $config->setMetadataCacheImpl($metadataCache);
    $config->setMetadataDriverImpl($metadataDriver);

    // Create cache dir
    if (!file_exists(\mym\PATH_TEMP . '/doctrine')) {
      mkdir(\mym\PATH_TEMP . '/doctrine', 0777, true);
    }

    //

    self::$instance = DM::create($connection, $config);
  }

  /**
   * @return \Doctrine\ODM\MongoDB\DocumentManager
   */
  public static function getInstance() {
    if (is_null(self::$instance)){
      self::_createInstance();
    }

    return self::$instance;
  }

  /**
   *
   * @return \MongoDB
   */
  public static function getMongoDB() {
    return self::getInstance()
        ->getConnection()
        ->getMongo()
        ->selectDB(\mym\PROJECT_NAME);
  }
}