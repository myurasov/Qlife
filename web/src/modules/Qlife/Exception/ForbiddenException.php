<?php

/**
 * Requested action is forbidden for the current user
 *
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Exception;

use mym\Exception\HTTPException;

class ForbiddenException extends HTTPException
{
  public function __construct($message = 'Forbidden', $code = 403, $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}