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
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\DeskproClientType;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\PusherType;
use DeskPRO\Bundle\AppBundle\Model\DeskproClientModel;
use DeskPRO\Bundle\AppBundle\Model\PusherModel;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\PusherLogger;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Firebase\JWT\JWT;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
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
     * @Rest\Get("/notify/setup/action-alerts/clients")
     */
    public function getClientCredentialsAction()
    {
        $config = $this->get('deskpro.notification.service')->getClientsSetup();

        $pusherEnabled  = count($config->getClients()) === 1 && $config->getClients()[0]->getType() === 'pusher';
        $deskproEnabled = count($config->getClients()) === 1 && $config->getClients()[0]->getType() === 'deskpro';
        $bag            = $this->get('settings_resolver')->getGlobalSettings();
        $pusherModel    = new PusherModel();

        $deskproClientModel = new DeskproClientModel();

        if ($pusherEnabled) {
            $mode = 'pusher';
        } elseif ($deskproEnabled) {
            $mode = 'deskpro';
        } else {
            $mode = 'db';
        }

        return View::create($this->wrap(
            [
                'mode'   => $mode,
                'pusher' => $pusherModel
                    ->setPusherEnabled($pusherEnabled)
                    ->setId($bag->get('notification.settings.pusher_client.appId', ''))
                    ->setSecret($bag->get('notification.settings.pusher_client.secret', ''))
                    ->setKey($bag->get('notification.settings.pusher_client.appKey', ''))
                    ->setCluster($bag->get('notification.settings.pusher_client.cluster', PusherModel::PUSHER_CLASTER_US_WEST_1)),

                'deskpro' => $deskproClientModel
                    ->setDeskproClientEnabled($deskproEnabled)
                    ->setSecret($bag->get('notification.settings.deskpro_client.secret', ''))
                    ->setHost($bag->get('notification.settings.deskpro_client.host', ''))
                    ->setPort($bag->get('notification.settings.deskpro_client.port', '')),
            ]
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
     *         {"name"="host", "description"="", "dataType"="string", "required"=false},
     *         {"name"="port", "description"="", "dataType"="string", "required"=false},
     *         {"name"="mode", "description"="", "dataType"="string", "required"=false}
     *     }
     * )
     *
     * @Rest\Put("/notify/setup/action-alerts/clients")
     *
     * @todo this is quick method, consider it hack
     *
     * @param Request $request
     *
     * @return View
     */
    public function saveClientsCredentialsAction(Request $request)
    {
        $mode = $request->request->get('mode');

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingRepo */
        $settingRepo = $this->get('doctrine.orm.default_entity_manager')->getRepository(Setting::class);

        $formData = $request->request->all();
        unset($formData['mode']);

        switch ($mode) {
            case 'pusher':
                $form = $this->createForm(PusherType::class);
                $form->submit($formData);

                if ($form->isValid()) {
                    /** @var PusherModel $pusherModel */
                    $pusherModel = $form->getData();
                    $settingRepo->updateSetting('notification.settings.pusher_client.appId', $pusherModel->getId());
                    $settingRepo->updateSetting('notification.settings.pusher_client.secret', $pusherModel->getSecret());
                    $settingRepo->updateSetting('notification.settings.pusher_client.appKey', $pusherModel->getKey());
                    $settingRepo->updateSetting('notification.settings.pusher_client.cluster', $pusherModel->getCluster());
                } else {
                    throw new InvalidFormException($form);
                }
                break;
            case 'deskpro':
                $form = $this->createForm(DeskproClientType::class);
                $form->submit($formData);

                if ($form->isValid()) {
                    /** @var DeskproClientModel $deskproClientModel */
                    $deskproClientModel = $form->getData();
                    $settingRepo->updateSetting('notification.settings.deskpro_client.host', $deskproClientModel->getHost());
                    $settingRepo->updateSetting('notification.settings.deskpro_client.secret', $deskproClientModel->getSecret());
                    $settingRepo->updateSetting('notification.settings.deskpro_client.port', $deskproClientModel->getPort());
                } else {
                    throw new InvalidFormException($form);
                }
                break;
            default:
                $mode = 'db';
        }

        $config = [
            'strategy' => 'immediate',
            'delivery' => [
                $mode,
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

    /**
     * @Rest\Post("/notify/setup/action-alerts/deskpro/test")
     *
     * @param Request $request
     *
     * @return View
     */
    public function testDeskproCredentialsAction(Request $request)
    {
        $form = $this->createForm(DeskproClientType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return View::create([
                'success' => false,
                'message' => 'Invalid settings were supplied. Make sure you have filled in all form fields.',
            ]);
        }

        /** @var DeskproClientModel $deskproClientModel */
        $deskproClientModel = $form->getData();

        $client = new HttpClient(
            [
                'base_uri' => sprintf('%s:%d',
                    $deskproClientModel->getHost(),
                    $deskproClientModel->getPort()),
            ]
        );

        $testData = ['test' => true];
        try {
            $response = $client->post(
                '/test',
                [
                    RequestOptions::JSON => ['jwt' => JWT::encode($testData, $deskproClientModel->getSecret())],
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        $message = $response->getBody()->getContents() ?: 'Can\'t connect to server';

        return View::create(['success' => $response->getStatusCode() === 200, 'message' => $message]);
    }
}
