<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PlivoLowCallbacksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/plivo_callbacks")
 * @ApiUserContext("open")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class PlivoLowCallbacksController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="User joins conference callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/user_joins_conference_callback", name="plivo_user_joins_conference_callback")
     */
    public function userJoinsConferenceCallbackAction()
    {
        // it's just a stub
        // we use a low script instead
    }
}
