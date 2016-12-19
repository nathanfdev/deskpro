<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Logs;

use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\EntityRepository\Setting as SettingRepo;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Logs\OptionsModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class LogsCrudController.
 *
 * @ApiModes("all")
 */
class LogsController extends BaseController
{
    /**
     * Gather logging options.
     *
     * @ApiDoc(
     *     section="Logs",
     *     resourceDescription="Operations about logs",
     *     description="get logging options",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Logs\OptionsModel"
     * )
     * @Rest\Get("/api_logs_options", name="api_logs_options")
     *
     * @return View
     */
    public function getOptionsAction()
    {
        $this->get('settings_resolver')->getGlobalSettings()->getBool('api_log.enabled');

        $options = new OptionsModel(
            $this->get('settings_resolver')->getGlobalSettings()->getBool('api_log.enabled'),
            $this->get('settings_resolver')->getGlobalSettings()->get('api_log.max_request_body_length'),
            $this->get('settings_resolver')->getGlobalSettings()->get('api_log.max_response_body_length'),
            $this->container->get('api_log.helper')->getModes()
        );

        return View::create(
            $this->wrap($options),
            Response::HTTP_OK
        );
    }

    /**
     * Update logging options.
     *
     * @ApiDoc(
     *     section="Logs",
     *     resourceDescription="Operations about logs",
     *     description="update logging options",
     *     requirements={
     *         {
     *             "name"="enabled",
     *             "requirement"="1|0",
     *             "dataType"="boolean",
     *             "description"="provide 1 if you want to enable logging"
     *         },
     *         {
     *             "name"="modes",
     *             "requirement"="(\w,)+",
     *             "dataType"="array",
     *             "description"="strings array, values are session, key, token"
     *         },
     *     },
     *     statusCodes={
     *         204="Returned if everything is OK",
     *     }
     * )
     *
     * @todo replace with form
     *
     * @Rest\Put("/api_logs_options", name="api_logs_options_update")
     *
     * @return View
     */
    public function putOptionsAction(Request $request)
    {
        $enabled        = $request->request->getBoolean('enabled');
        $modes          = $request->request->get('modes');
        $requestLength  = $request->request->get('request_length');
        $responseLength = $request->request->get('response_length');

        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var SettingRepo $repo */
        $repo = $em->getRepository('\Application\DeskPRO\Entity\Setting');

        if (!$setting = $repo->findOneBy(['name' => 'api_log.enabled'])) {
            $setting       = new Setting();
            $setting->name = 'api_log.enabled';
        }
        /* @var Setting $setting */
        $setting->value = $enabled;

        if (!$modesSetting = $repo->findOneBy(['name' => 'api_log.modes'])) {
            $modesSetting       = new Setting();
            $modesSetting->name = 'api_log.modes';
        }
        /* @var Setting $setting */
        $modesSetting->value = serialize($modes);

        if (!$requestLengthSetting = $repo->findOneBy(['name' => 'api_log.max_request_body_length'])) {
            $requestLengthSetting       = new Setting();
            $requestLengthSetting->name = 'api_log.max_request_body_length';
        }
        /* @var Setting $setting */
        $requestLengthSetting->value = $requestLength;

        if (!$responseLengthSetting = $repo->findOneBy(['name' => 'api_log.max_response_body_length'])) {
            $responseLengthSetting       = new Setting();
            $responseLengthSetting->name = 'api_log.max_response_body_length';
        }
        /* @var Setting $setting */
        $responseLengthSetting->value = $responseLength;

        $em->persist($setting);
        $em->persist($modesSetting);
        $em->persist($requestLengthSetting);
        $em->persist($responseLengthSetting);
        $em->flush();

        return View::create(
            null,
            Response::HTTP_NO_CONTENT,
            ['Location' => $this->generateUrl('api_logs_options')]
        );
    }

    /**
     * This endpoint gives you an ability to replay log entry.
     *
     * **Subrequest** mode means that log will be replayed with framework only
     *
     * **Isolated** mode means that log will be replayed with curl, make sure you have it
     *
     * @ApiDoc(
     *     section="Logs",
     *     resourceDescription="Operations about logs",
     *     description="update logging options",
     *     requirements={
     *         {
     *             "name"="mode",
     *             "requirement"="isolated|subrequest",
     *             "dataType"="string",
     *             "description"="how to replay",
     *             "default"="subrequest",
     *         },
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "dataType"="integer",
     *             "description"="id of entry to replay"
     *         },
     *     },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *         400="Returned if mode not equals 'isolated' or 'subrequest'",
     *         404="We can't find entry with provided id",
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\ApiLog"
     * )
     *
     * @Rest\Post("/api_logs/{id}/replay", name="api_logs_replay", requirements={"id": "\d+"})
     * @Rest\View(serializerGroups={"list", "details"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function replayLogAction(Request $request, $id)
    {
        $mode = $request->request->get('mode', 'subrequest');

        if (!$log = $this->get('doctrine.orm.default_entity_manager')->find('\DeskPRO\Bundle\AppBundle\Entity\ApiLog', $id)) {
            throw new NotFoundHttpException(sprintf('Log with id [ %d ] not found', $id));
        }

        switch ($mode) {
            case 'subrequest':
                $log = $this->get('api_log.replayer')->replay($log->getRequestId(), 'null');
                break;
            case 'isolated':
                $log = $this->get('api_log.replayer')->replayWithCrawler($log->getRequestId(), 'null');
                break;
            default:
                throw new BadRequestHttpException();
        }

        return View::create(
            $this->wrap($log),
            Response::HTTP_OK
        );
    }
}
