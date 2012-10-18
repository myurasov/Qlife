<?php

/**
 * 'mym\Helper' namespace configuration options
 *
 * @copyright 2012 Mikhail Yurasov
 * @package mym
 */

namespace mym\Helper;

class Config
{
  public static $options;

  /**
   * Called once on config file load
   */
  public static function selfInit()
  {
    static::$options = array(
    );
  }
}

// Self-initialize
Config::selfInit();