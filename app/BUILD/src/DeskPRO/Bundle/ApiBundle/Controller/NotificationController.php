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

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\PusherType;
use DeskPRO\Bundle\AppBundle\Model\PusherModel;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\PusherLogger;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Orb\Logger\Handler\ArrayHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotificationController.
 *
 * @ApiModes("all")
 * @ApiUserContext("agent", admin={"savePusherCredentialsAction", "getPusherCredentialsAction", "testPusherCredentialsAction"})
 */
class NotificationController extends BaseController
{
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
     * @Rest\Get("/notify/setup/action-alerts")
     */
    public function setupActionAlertsAction()
    {
        return View::create($this->wrap($this->get('deskpro.notification.service')->getClientsSetup()));
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
     * @Rest\Post("/pusher/auth")
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

    /**
     * This endpoint provide you an ability to get pusher credentials you are using (admin only).
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
     *     output="DeskPRO\Bundle\AppBundle\Model\PusherModel>"
     * )
     *
     * @return View
     *
     * @todo move it to standalone controller
     * @Rest\Get("/notify/setup/action-alerts/pusher")
     */
    public function getPusherCredentialsAction()
    {
        $config        = $this->get('deskpro.notification.service')->getClientsSetup();
        $pusherEnabled = count($config->getClients()) === 1 && $config->getClients()[0]->getType() === 'pusher';
        $bag           = $this->get('settings_resolver')->getGlobalSettings();
        $pusherModel   = new PusherModel();

        return View::create($this->wrap(
            $pusherModel
                ->setPusherEnabled($pusherEnabled)
                ->setId($bag->get('notification.settings.pusher_client.appId', ''))
                ->setSecret($bag->get('notification.settings.pusher_client.secret', ''))
                ->setKey($bag->get('notification.settings.pusher_client.appKey', ''))
                ->setCluster($bag->get('notification.settings.pusher_client.cluster', PusherModel::PUSHER_CLASTER_US_WEST_1))
        ));
    }

    /**
     * Save pusher credentials and enable/disable it.
     *
     * @ApiDoc(
     *     section="Notifications and alerts",
     *     resourceDescription="Operations about action alerts",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *      parameters={
     *         {"name"="id", "description"="", "dataType"="string", "required"=false},
     *         {"name"="key", "description"="", "dataType"="string", "required"=false},
     *         {"name"="secret", "description"="", "dataType"="string", "required"=false},
     *         {"name"="pusher_enabled", "description"="", "dataType"="boolean", "required"=false}
     *     }
     * )
     *
     * @Rest\Put("/notify/setup/action-alerts/pusher")
     *
     * @todo this is quick method, consider it hack
     *
     * @param Request $request
     *
     * @return View
     */
    public function savePusherCredentialsAction(Request $request)
    {
        $form = $this->createForm(PusherType::class);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            /** @var \Application\DeskPRO\EntityRepository\Setting $settingRepo */
            $settingRepo = $this->get('doctrine.orm.default_entity_manager')->getRepository(Setting::class);
            /** @var PusherModel $pusherModel */
            $pusherModel = $form->getData();
            $settingRepo->updateSetting('notification.settings.pusher_client.appId', $pusherModel->getId());
            $settingRepo->updateSetting('notification.settings.pusher_client.secret', $pusherModel->getSecret());
            $settingRepo->updateSetting('notification.settings.pusher_client.appKey', $pusherModel->getKey());
            $settingRepo->updateSetting('notification.settings.pusher_client.cluster', $pusherModel->getCluster());

            $config = [
                'strategy' => 'immediate',
                'delivery' => [
                    $pusherModel->isPusherEnabled() ? 'pusher' : 'db',
                ],
            ];
            $settingRepo->updateSetting('notification.settings.default_strategy', serialize($config));

            // this should work for immediate only, cause notification handlers already has been built
            $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload',
                    [
                        'type'        => 'admin',
                        'person_id'   => 0,
                        'person_name' => 'System',
                    ])
            );
        }

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/notify/setup/action-alerts/pusher/test")
     *
     * @param Request $request
     *
     * @return View
     */
    public function testPusherCredentialsAction(Request $request)
    {
        $form = $this->createForm(PusherType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return View::create([
                'success' => false,
                'message' => 'Invalid settings were supplied. Make sure you have filled in all form fields.',
            ]);
        }

        /** @var PusherModel $pusherModel */
        $pusherModel = $form->getData();

        $auth_key = $pusherModel->getKey();
        $secret   = $pusherModel->getSecret();
        $app_id   = $pusherModel->getId();

        $p = new \Pusher($auth_key, $secret, $app_id, ['cluster' => $pusherModel->getCluster()]);

        $handler = new ArrayHandler();
        $handler->setFormatter(new LineFormatter('[%datetime%] %message%'));
        $logger = new Logger('PusherTest', [$handler]);
        $p->set_logger(new PusherLogger($logger));

        $success = $p->trigger(['private-channel-test'], 'test', 'test');
        $message = $handler->getMessagesAsString();

        return View::create(['success' => $success, 'message' => $message]);
    }
}
