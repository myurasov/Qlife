<?php

/**
 * Device API
 * @copyright 2012, Mikhail Yurasov
 */

namespace Qlife\Controller\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Qlife\Controller\ControllerAbstract;
use Qlife\Service\DocumentManager;

class DeviceController extends ControllerAbstract {

  /**
   * ?id - device is
   * ?isPrivate - 1/0
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function updateAction(Request $request) {
    $response = new JsonResponse();

    $user = $this->authenticate($request, $response, true);
    $device = $this->loadRequestedDocumentById($request, 'id', 'Device\DeviceAbstract', true, true);

    if ($request->query->has('isPrivate')) {
      $device->setIsPrivate((bool) $request->query->get('isPrivate'));
    }

    $dm = DocumentManager::getInstance();
    $dm->flush($device);

    $response->setData($device->toArray());

    return $response;
  }

}