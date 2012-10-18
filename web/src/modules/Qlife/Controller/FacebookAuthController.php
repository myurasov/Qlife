<?php

/**
 * Facebook authentication controller
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Controller;

use mym\Component\Facebook\Facebook;
use mym\Component\Facebook\FacebookAuthControllerAbstract;
use Qlife\Service\DocumentManager;
use Qlife\Config;
use Qlife\Document\User;

class FacebookAuthController extends FacebookAuthControllerAbstract {

  public function __construct() {
    // config
    $this->setFacebookConfig(Config::$options['Facebook']);

    // access scope
    $this->setScope('user_birthday,email');

    // callback url
    $this->setCallbackUrl(
      'http://' .
      Config::$options['baseUrl'] .
      '/Auth/callback'
    );
  }

  public function onAuthenticate($accessToken) {

    // start session
    session_start();

    // create FB
    $fb = new Facebook(Config::$options['Facebook']);
    $fb->setAccessToken($accessToken);

    // get user data
    $facebookProfileData = $fb->graphApi('me');

    // search for existing user

    $dm = DocumentManager::getInstance();
    $user = $dm->getRepository('Qlife\Document\User')
      ->findOneBy(array('facebookId' => $facebookProfileData->id));

    //

    if (is_null($user)) { // new user
      $user = new User();
      $dm->persist($user);

      // update user data

      $user->setName($facebookProfileData->name);
      $user->setFirstName($facebookProfileData->first_name);
      $user->setLastName($facebookProfileData->last_name);
      $user->setFacebookId($facebookProfileData->id);
      $user->setEmail($facebookProfileData->email);
      $user->setFacebookAccessToken($accessToken);

      // gender
      if ($facebookProfileData->gender == 'male') {
        $user->setGender(User::GENDER_MALE);
      } else if ($facebookProfileData->gender == 'female') {
        $user->setGender(User::GENDER_FEMALE);
      } else {
        $user->setGender(User::GENDER_UNKNOWN);
      }

      if ($facebookProfileData->birthday) {
        $user->setBirthday(new \DateTime($facebookProfileData->birthday));
      }

      $user->createReadableId();
      $user->registerWithTwoNet();
      $user->addDemoDevices();

    } else {
      //
    }

    //

    // redirect to user profile - /<user>
    $this->setReturnUrl('/' . $user->getReadableId());

    // save
    $dm->flush();

    // save userId to session
    $_SESSION['userId'] = $user->getId();
  }
}