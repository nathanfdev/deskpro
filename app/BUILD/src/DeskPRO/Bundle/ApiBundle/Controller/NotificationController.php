<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\Limits\Annotation\ApiDisableLimits;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NotificationController.
 *
 * @ApiModes("all")
 */
class NotificationController extends BaseController
{
    /**
     * Fetch list of alerts was rised after last check.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about action alerts",
     *     requirements={
     *          {
     *              "name"="last",
     *              "requirement"="\d+",
     *              "description"="last alert timestamp",
     *              "dataType"="integer"
     *          }
     *      },
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\ActionAlert>"
     * )
     *
     * @param string  $last
     * @param Request $request
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return View
     * @Rest\Get("/notify/action-alerts/{last}", name="action_alerts_last")
     */
    public function getLastActionAlertsAction($last, Request $request)
    {
        $service = $this->get('deskpro.notification.service');
        $this->doHeartbeat($request);

        $alerts = $service->getLastActionAlerts($last, $this->getUser());

        return View::create($this->wrap($alerts));
    }

    /**
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about action alerts",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationConfiguration"
     * )
     *
     * @return View
     * @Rest\Get("/notify/setup/action-alerts", name="action_alerts_setup")
     */
    public function setupActionAlertsAction()
    {
        return View::create($this->wrap($this->get('deskpro.notification.service')->getClientsSetup()));
    }

    /**
     * Used for internal purposes to update online status.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about action alerts",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @param Request $request
     *
     * @ApiDisableLimits()
     * @Rest\Put("/notify/heartbeat", name="online_heartbeat")
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function heartbeatAction(Request $request)
    {
        $this->doHeartbeat($request);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     */
    protected function doHeartbeat(Request $request)
    {
        $session_code = $request->cookies->get('dpsid-agent');
        /** @var \Application\DeskPRO\EntityRepository\Session $repository */
        $repository = $this->getDoctrine()->getRepository(Session::class);
        $session    = $repository->getSessionFromCode($session_code);

        if ($session) {
            $session->updateLastTime();
            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->persist($session);
            $em->flush();
        }
    }

    /**
     * This endpoint provide you an ability to authenticate pusher app.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about action alerts",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     parameters={
     *         {"name"="user_id", "description"="", "dataType"="integer", "required"=true},
     *         {"name"="channel_name", "description"="", "dataType"="string", "required"=true},
     *         {"name"="socket_id", "description"="", "dataType"="string", "required"=true}
     *     },
     *     output="array"
     * )
     *
     * @Rest\Post("/pusher/auth", name="pusher_auth")
     *
     * @param Request $request
     *
     * @return View
     */
    public function pusherAuthAction(Request $request)
    {
        $submitted = $request->request->all();
        /** @var \Pusher $pusher */
        $pusher = $this->get('deskpro.notification.pusher');
        $user   = $this->getUser();
        if ($user->getId() === (int) $submitted['user_id']) {
            $status = Response::HTTP_OK;
            $data   = json_decode($pusher->socket_auth($submitted['channel_name'], $submitted['socket_id']), true);
        } else {
            $data   = [];
            $status = Response::HTTP_FORBIDDEN;
        }

        return View::create($data, $status);
    }
}
