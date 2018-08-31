<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\ExternalEvents;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent\PopupEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PopupController.
 *
 * @ApiDoc(target="all", section="ExternalEvents", output="Application\DeskPRO\Entity\Popup")
 *
 * @ApiModes("all")
 * @Rest\Route("/external_events/popup")
 * @ApiUserContext("admin")
 */
class PopupController extends BaseController
{
    /**
     * This endpoint provide you an ability to authenticate pusher app.
     *
     * @ApiDoc(
     *     section="External events",
     *     resourceDescription="Operations about external events",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/raise")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function raiseAction(Request $request)
    {
        $event = new PopupEvent(PopupEvent::ACTION_TYPE_RAISE, $request->request->all());
        $this->getContainer()->get('event_dispatcher')->dispatch(
            PopupEvent::EVENT_NAME,
            $event
        );

        return View::create($this->wrap(['uuid' => $event->getUuid()]), Response::HTTP_OK);
    }

    /**
     * This endpoint provide you an ability to authenticate pusher app.
     *
     * @ApiDoc(
     *     section="External events",
     *     resourceDescription="Operations about external events",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Delete("/dismiss/{uuid}")
     *
     * @param Request $request
     * @param string  $uuid
     *
     * @throws \Exception
     *
     * @return View
     */
    public function dismissAction(Request $request, $uuid)
    {
        $this->getContainer()->get('event_dispatcher')->dispatch(
            PopupEvent::ACTION_TYPE_DISMISS,
            ['uuid' => $uuid]
        );

        return View::create([], Response::HTTP_NO_CONTENT);
    }
}
