<?php

/**
 * Controller abstract
 *
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Qlife\Service\DocumentManager;
use Qlife\Document\User;
use Qlife\Exception\ForbiddenException;
use Qlife\Exception\BadArgumentsException;
use mym\Exception\NotFoundException;

abstract class ControllerAbstract {

  protected $user;

  /**
   * Check if user is authenticated.
   * Modifies response to be private/public.
   *
   * @param Request $request
   * @param Response $response
   * @param bool $userRequired
   * @throws ForbiddenException
   * @return User Current user
   */
  protected function authenticate(Request $request, Response &$response, $userRequired = false) {

    // check auth

    $user = null;

    if ($request->cookies->has(session_name())) {
      $response->setPrivate();
      session_start();

      if (isset($_SESSION['userId'])) {
        // fetch user from db
        $dm = DocumentManager::getInstance();
        $user = $dm->find('Qlife\Document\User', $_SESSION['userId']);
      }

      if (is_null($user)) {
        // delete userless session
        $response->headers->setCookie(new Cookie(session_name(), null));
        session_destroy();
      }
    } else {
      $response->setPublic();
    }

    if ($userRequired && is_null($user))
      throw new ForbiddenException('User login is required');

    return $user;
  }

  /**
   * Get requested resource
   *
   * @param Request $request
   * @param str $idName
   * @param str $document
   * @param bool $badArgumentsException
   * @param bool $notFoundException
   * @throws BadArgumentsException
   * @throws NotFoundException
   */
  protected function loadRequestedDocumentById(
    Request $request, $idName, $document, $badArgumentsException = true, $notFoundException = true
  ) {

    if ($request->query->has($idName)) {
      $document = DocumentManager::getInstance()
        ->find('Qlife\Document\\' . $document, $request->query->get($idName));
    } else if ($badArgumentsException) {
      throw new BadArgumentsException($idName . ' is required');
    }

    if (is_null($document) && $notFoundException) {
      throw new NotFoundException($document . ' not found');
    }

    return $document;
  }
}