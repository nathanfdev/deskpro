<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

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
     * @Rest\Post("/async_callback", name="plivo_async_callback")
     *
     * @param Request $request
     */
    public function asyncCallbackAction(Request $request)
    {
        $this->get('dp.voice.logger')->info(sprintf('[PlivoLowCallbacksController] Async callback, params %s', json_encode($request->request->all())));
    }
}
