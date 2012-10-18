<?php

/**
 * Bad argumenmts exception
 *
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Exception;

use mym\Exception\HTTPException;

class BadArgumentsException extends HTTPException
{
  public function __construct($message = 'Bad arguments', $code = 400, $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}