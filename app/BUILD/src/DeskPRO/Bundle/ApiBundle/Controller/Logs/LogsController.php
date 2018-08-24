<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Logs;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Logs\ApiLogsOptionsType;
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
        return View::create($this->wrap($this->getOptionsModel()));
    }

    /**
     * Update logging options.
     *
     * @ApiDoc(
     *     section="Logs",
     *     resourceDescription="Operations about logs",
     *     description="update logging options",
     *     statusCodes={
     *         204="Returned if everything is OK",
     *     },
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Logs\ApiLogsOptionsType",
     *      "options"={
     *          "data"="DeskPRO\Bundle\AppBundle\Serializer\Model\Logs\OptionsModel"
     *      }
     *     }
     * )
     *
     * @Rest\Put("/api_logs_options", name="api_logs_options_update")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function putOptionsAction(Request $request)
    {
        $form = $this->createForm(ApiLogsOptionsType::class, $this->getOptionsModel());
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var OptionsModel $data */
        $data = $form->getData();

        $em   = $this->get('doctrine.orm.default_entity_manager');
        $repo = $em->getRepository(Setting::class);

        // update enabled setting
        if (!$enabledSetting = $repo->findOneBy(['name' => 'api_log.enabled'])) {
            $enabledSetting = new Setting();
            $enabledSetting->setName('api_log.enabled');
        }

        $enabledSetting->setValue($data->isEnabled());

        // update modes setting
        if (!$modesSetting = $repo->findOneBy(['name' => 'api_log.modes'])) {
            $modesSetting = new Setting();
            $modesSetting->setName('api_log.modes');
        }

        $modesSetting->setValue(serialize($data->getModes()));

        // update request length setting
        if (!$requestLengthSetting = $repo->findOneBy(['name' => 'api_log.max_request_body_length'])) {
            $requestLengthSetting = new Setting();
            $requestLengthSetting->setName('api_log.max_request_body_length');
        }

        $requestLengthSetting->setValue($data->getRequestLength() ?: 0);

        // update response length setting
        if (!$responseLengthSetting = $repo->findOneBy(['name' => 'api_log.max_response_body_length'])) {
            $responseLengthSetting = new Setting();
            $responseLengthSetting->setName('api_log.max_response_body_length');
        }

        $responseLengthSetting->setValue($data->getResponseLength() ?: 0);

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
     * @throws \Exception
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

    /**
     * @return OptionsModel
     */
    private function getOptionsModel()
    {
        $options = new OptionsModel();
        $options
            ->setEnabled((bool) $this->container->get('api_log.helper')->isLoggingEnabled())
            ->setRequestLength((int) $this->container->get('api_log.helper')->getMaxRequestBodyLength())
            ->setResponseLength((int) $this->container->get('api_log.helper')->getMaxResponseBodyLength())
            ->setModes($this->container->get('api_log.helper')->getModes())
        ;

        return $options;
    }
}
