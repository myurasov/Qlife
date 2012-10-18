<?php

/**
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife;

use mym\ConfigBase;

class Config
{
  public static $options;

  public static function selfInit()
  {
    static::$options = array(

      // libs
      'libraries' => array(
        'Twig'                  => '/Projects/_libraries/php/Twig',
        'Doctrine_MongoDB_ODM'  => '/Projects/_libraries/php/Doctrine/mongodb-odm',
        'Doctrine_MongoDB'      => '/Projects/_libraries/php/Doctrine/mongodb',
        'Doctrine_Common'       => '/Projects/_libraries/php/Doctrine/common'
      ),

      // base url, not including protocol
      'baseUrl' => (\mym\HOSTNAME == 'qlife.local')
        ? 'qlife.local'
        : 'qlife.us',

      // session control
      'sessionName' => '__session',
      'sessionTimeout' => 3600 * 24 * 14, // 2 weeks

      // facebook api credentials
      'Facebook' => (\mym\HOSTNAME == 'qlife.local')
        ? array(
          'appId' => '452626804780618', // Qlife-dev
          'secret' => '0891a89f489eebee0059acf8b90003c1',
          'certFile' => \mym\PATH_RESOURCES . '/fb_ca_chain_bundle.crt'
        )
        : array(
          'appId' => '290615814372263', // Qlife
          'secret' => '6907cc35e5319a1168e43228e92fb8ab',
          'certFile' => \mym\PATH_RESOURCES . '/fb_ca_chain_bundle.crt'
        ),

      // mongodb server connection string
      'mongoServer' => 'mongodb://localhost:27017',

      // 2net API auth
      'TwoNet' => array(
        'key' => '8pjUu4nwE1mKu315GDIa',
        'secret' => 'e5E73dFW9Z5MeKh4lpKiVo7nGDNCkax4'
      ),

      // period for the recent data for devices [s]
      'recentDataPeriod' => 60 * 60 * 24 * 7

    );
  }
}

Config::selfInit();