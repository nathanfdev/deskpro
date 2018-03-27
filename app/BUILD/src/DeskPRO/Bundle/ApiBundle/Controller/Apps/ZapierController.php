<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class ZapierController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/zapier")
 */
class ZapierController extends BaseController
{
    /**
     * Gather specific info about authentication.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="ping and if it not the case install Zapier app",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noOutput=true
     * )
     * @Rest\Get("/ping")
     */
    public function pingAction()
    {
        return View::create([], 200);
    }
}
