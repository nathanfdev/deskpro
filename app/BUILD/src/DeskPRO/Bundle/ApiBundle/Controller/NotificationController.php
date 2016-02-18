<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Pusher;
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
     * @param string  $last
     * @param Request $request
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return View
     * @Annotations\Get("/notify/action-alerts/{last}", name="action_alerts_last")
     */
    public function getLastActionAlertsAction($last, Request $request)
    {
        $service = $this->get('deskpro.notification.service');
        $this->doHeartbeat($request);

        $alerts = $service->getLastActionAlerts($last, $this->getUser());

        return View::create(
            $this->dataSerialize($alerts),
            Response::HTTP_OK
        );
    }

    /**
     * @return View
     * @Annotations\Get("/notify/setup/action-alerts", name="action_alerts_setup")
     */
    public function setupActionAlertsAction()
    {
        $service  = $this->get('deskpro.notification.service');
        $response = [
            'clients' => $service->getClientsSetup(),
        ];

        return View::create(
            $this->createRepresentation($response),
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     *
     * @return View
     * @Annotations\Get("/notify/heartbeat", name="online_heartbeat")
     */
    public function heartbeatAction(Request $request)
    {
        $this->doHeartbeat($request);

        return View::create(
            $this->createRepresentation([]),
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     */
    protected function doHeartbeat(Request $request)
    {
        $session_code = $request->cookies->get('dpsid-agent');
        /** @var \Application\DeskPRO\EntityRepository\Session $session_repository */
        $session_repository = $this->getDoctrine()->getRepository('DeskPRO:Session');
        $session            = $session_repository->getSessionFromCode($session_code);
        if ($session) {
            $session->updateLastTime();
            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->persist($session);
            $em->flush();
        }
    }

    /**
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return View
     * @Annotations\Post("/pusher/auth", name="pusher_auth")
     */
    public function pusherAuthAction(Request $request)
    {
        $submitted = $request->request->all();
        /** @var Pusher $pusher */
        $pusher = $this->get('deskpro.notification.pusher');
        $user   = $this->getUser();
        if ($user->getId() === (int) $submitted['user_id']) {
            $status = Response::HTTP_OK;
            $data   = json_decode($pusher->socket_auth($submitted['channel_name'], $submitted['socket_id']), true);
        } else {
            $data   = [];
            $status = Response::HTTP_FORBIDDEN;
        }

        return View::create(
            $data, $status
        );
    }
}
