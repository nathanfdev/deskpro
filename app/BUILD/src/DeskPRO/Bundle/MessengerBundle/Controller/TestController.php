<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class TestController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 */
class TestController extends BaseController
{
    /**
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Testing new Bundle and Kernel",
     *     resourceDescription="Testing new Bundle and Kernel",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noOutput=true
     * )
     *
     * @return View
     * @Rest\Get("/ping")
     */
    public function pingAction()
    {
        return View::create($this->wrap(['response' => 'pong']));
    }
}
