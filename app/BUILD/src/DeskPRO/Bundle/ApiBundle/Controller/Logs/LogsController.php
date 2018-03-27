<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Logs;

use Application\DeskPRO\Entity\Setting;
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

        return View::create($this->wrap($options));
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
     *     },
     *     parameters={
     *         {"name"="enabled", "description"="", "dataType"="boolean", "required"=true},
     *         {"name"="modes", "description"="", "dataType"="array", "required"=true},
     *         {"name"="request_length", "description"="", "dataType"="integer", "required"=true},
     *         {"name"="response_length", "description"="", "dataType"="integer", "required"=true}
     *      }
     * )
     *
     * @todo replace with form
     *
     * @Rest\Put("/api_logs_options", name="api_logs_options_update")
     *
     * @param Request $request
     *
     * @return View
     */
    public function putOptionsAction(Request $request)
    {
        $enabled        = $request->request->getBoolean('enabled');
        $modes          = $request->request->get('modes');
        $requestLength  = $request->request->get('request_length');
        $responseLength = $request->request->get('response_length');

        $em   = $this->get('doctrine.orm.default_entity_manager');
        $repo = $em->getRepository(Setting::class);

        // update enabled setting
        if (!$enabledSetting = $repo->findOneBy(['name' => 'api_log.enabled'])) {
            $enabledSetting = new Setting();
            $enabledSetting->setName('api_log.enabled');
        }

        $enabledSetting->setValue($enabled);

        // update modes setting
        if (!$modesSetting = $repo->findOneBy(['name' => 'api_log.modes'])) {
            $modesSetting = new Setting();
            $modesSetting->setName('api_log.modes');
        }

        $modesSetting->setValue(serialize($modes));

        // update request length setting
        if (!$requestLengthSetting = $repo->findOneBy(['name' => 'api_log.max_request_body_length'])) {
            $requestLengthSetting = new Setting();
            $requestLengthSetting->setName('api_log.max_request_body_length');
        }

        $requestLengthSetting->setValue($requestLength);

        // update response length setting
        if (!$responseLengthSetting = $repo->findOneBy(['name' => 'api_log.max_response_body_length'])) {
            $responseLengthSetting = new Setting();
            $responseLengthSetting->setName('api_log.max_response_body_length');
        }

        $responseLengthSetting->setValue($responseLength);

        $em->persist($enabledSetting);
        $em->persist($modesSetting);
        $em->persist($requestLengthSetting);
        $em->persist($responseLengthSetting);
        $em->flush();

        return View::create(null, Response::HTTP_NO_CONTENT, ['Location' => $this->generateUrl('api_logs_options')]);
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
     *     output="DeskPRO\Bundle\AppBundle\Entity\ApiLog",
     *     parameters={
     *         {"name"="mode", "description"="", "dataType"="string", "required"=true}
     *     }
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

        return View::create($this->wrap($log));
    }
}
