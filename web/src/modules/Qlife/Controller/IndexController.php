<?php

/**
 * Index controller
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Qlife\Service\TwigService;
use Qlife\Service\DocumentManager;

class IndexController extends ControllerAbstract {
  /**
   * Index
   */
  public function indexAction(Request $request) {
    $response = new Response();
    $user = $this->authenticate($request, $response);
    $response->setPrivate(); // it's a web app!

    // get all users
    $dm = DocumentManager::getInstance();
    $profileUsers = $dm->getRepository('Qlife\Document\User')
      ->findBy(array(), array('createdAt' => 'ASC'), 15);

    $data = array(
      'user' => $user,
      'profileUsers' => $profileUsers
    );

    $response->setContent(
      TwigService::getTwigEnviroment()->render('Index.twig', $data)
    );

    return $response;
  }

  public function _indexAction(Request $request) {
    $response = new Response();
    $response->setPrivate();
    $response->setContent('<script>location.href = "/misha"</script>');
    return $response;
  }
}