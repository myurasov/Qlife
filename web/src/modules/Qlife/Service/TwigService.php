<?php

/**
 * Twig service
 *
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Service;

use Qlife\Config;

class TwigService
{
  /**
   * @var \Twig_Environment
   */
  private static $twigEnviroment;

  /**
   * @return \Twig_Environment
   */
  public static function getTwigEnviroment()
  {
    if (is_null(self::$twigEnviroment))
      self::loadTwig();

    return self::$twigEnviroment;
  }

  /**
   * Loads Twig libary and registers it's class autoloader
   */
  private static function loadTwig()
  {
    if (!class_exists('Twig_Autoloader', false))
    {
      require Config::$options['libraries']['Twig'] . '/lib/Twig/Autoloader.php';
      \Twig_Autoloader::register();

      $loader = new \Twig_Loader_Filesystem(\mym\PATH_TEMPLATES);

      self::$twigEnviroment = new \Twig_Environment($loader,
        array(
            'debug'               => \mym\DEVELOPMENT ? true : false,
            'charset'             => 'UTF-8',
            'base_template_class' => 'Twig_Template',
            'strict_variables'    => false,
            'autoescape'          => 'html',
            'cache'               => \mym\PATH_TEMP . '/TwigCache',
            'auto_reload'         => null,
            'optimizations'       => -1,
          )
      );

      // debug extension

      if (\mym\DEVELOPMENT)
        self::$twigEnviroment->addExtension(new \Twig_Extension_Debug());

      // custom filters

      self::$twigEnviroment->addFilter('timeAgo',
        new \Twig_Filter_Function(__CLASS__ . '::filter_timeAgo'));

      self::$twigEnviroment->addFilter('formatPrice',
        new \Twig_Filter_Function(__CLASS__ . '::filter_formatPrice'));

      self::$twigEnviroment->addFilter('toJson',
        new \Twig_Filter_Function(__CLASS__ . '::filter_toJson'));

      self::$twigEnviroment->addFilter('ellipsize',
        new \Twig_Filter_Function(__CLASS__ . '::filter_ellipsize'));
    }
  }

  /**
   * Trim string to the width, adding ... at the end
   *
   * @return string
   */
  public function filter_ellipsize($var, $width)
  {
    return mb_strimwidth($var, 0, $width, '...', 'UTF-8');
  }

  /**
   * Convert variable to json
   * On documents toJson() method is called
   *
   * @return string
   */
  public function filter_toJson($var)
  {
    if (is_object($var) && method_exists($var, 'toJson'))
    {
      $args = func_get_args();
      array_shift($args);
      return $var->toJson($args);
    }
    else
    {
      return json_encode($var);
    }
  }

  /**
   * Formats price as: 1,234.50
   *
   * @param string$var
   */
  public function filter_formatPrice($var)
  {
    if (is_null($var))
      return '';

    // $var is a string representation of price in cents
    $var = floatval($var) /  100;
    return number_format($var, 2, '.', ',');
  }

  /**
   *
   * Create passed time string
   *
   * @param float|\DateTime $seconds
   * @return string
   */
  public static function filter_timeAgo($seconds)
  {
    if ($seconds instanceof \DateTime)
      $seconds = time() - $seconds->getTimestamp();

    $settings = array(
      'allowFuture' => false,
      'strings' => array(
        'prefixAgo' => "",
        'prefixFromNow' => "",
        'suffixAgo' => "",
        'suffixFromNow' => "",
        'second' => "a second ago",
        'seconds' => "%d seconds ago",
        'minute' => "about a minute ago",
        'minutes' => "%d minutes ago",
        'hour' => "about an hour ago",
        'hours' => "about %d hours ago",
        'day' => "a day ago",
        'days' => "%d days ago",
        'month' => "about a month ago",
        'months' => "%d months ago",
        'year' => "about a year ago",
        'years' => "%d years ago"
      )
    );

    $seconds = floatval($seconds);
    $l = $settings['strings'];
    $prefix = $l['prefixAgo'];
    $suffix = $l['suffixAgo'];

    if ($settings['allowFuture'])
    {
      if ($seconds < 0)
      {
        $prefix = $l['prefixFromNow'];
        $suffix = $l['suffixFromNow'];
      }
      $seconds = abs($seconds);
    }

    $minutes = $seconds / 60;
    $hours = $minutes / 60;
    $days = $hours / 24;
    $years = $days / 365;

    if ($seconds < 1.5)
      $words = $l['second'];
    else if ($seconds < 60)
      $words = sprintf($l['seconds'], round($seconds));
    else if ($seconds < 90)
      $words = sprintf($l['minute'], 1);
    else if ($minutes < 45)
      $words = sprintf($l['minutes'], round($minutes));
    else if ($minutes < 90)
      $words = sprintf($l['hour'], 1);
    else if ($hours < 24)
      $words = sprintf($l['hours'], round($hours));
    else if ($hours < 48)
      $words = sprintf($l['day'], 1);
    else if ($days < 30)
      $words = sprintf($l['days'], floor($days));
    else if ($days < 60)
      $words = sprintf($l['month'], 1);
    else if ($days < 365)
      $words = sprintf($l['months'], floor($days / 30));
    else if ($years < 2)
      $words = sprintf($l['year'], 1);
    else
      $words = sprintf($l['years'], floor($years));

    return trim(join(' ', array($prefix, $words, $suffix)));
  }
}