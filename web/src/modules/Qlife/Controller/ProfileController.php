<?php

/**
 * User profile controller
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Qlife\Service\TwigService;
use Qlife\Service\DocumentManager;
use mym\Exception\NotFoundException;

class ProfileController extends ControllerAbstract {
  public function indexAction(Request $request) {
    $response = new Response();
    $user = $this->authenticate($request, $response);
    $response->setPrivate();

    $dm = DocumentManager::getInstance();

    // get profile user

    $profileUser = null;
    $userCanEditProfile = $user && $user->getIsAdmin();
    $userOwnsProfile = false;

    if ($user && $user->getReadableId() == $request->query->get('userReadableId')) {
      $profileUser = $user;
      $userCanEditProfile = true;
      $userOwnsProfile = true;
    } else {
      $profileUser = $dm->getRepository('Qlife\Document\User')->findOneBy(array(
        'readableId' => $request->query->get('userReadableId')
      ));
    }

    if (is_null($profileUser)) {
      throw new NotFoundException('Profile not found');
    }

    //

    $data = array(
      'user' => $user,
      'profileUser' => $profileUser,
      'userCanEditProfile' => $userCanEditProfile,
      'userOwnsProfile' => $userOwnsProfile
    );

    $response->setContent(
      TwigService::getTwigEnviroment()->render('Profile.twig', $data)
    );

    return $response;
  }
}