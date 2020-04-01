<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceOutboundCallType;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioClientTokens;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles client actions.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_client")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceClientController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Returns client voice auth tokens",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioClientTokens"
     * )
     *
     * @Rest\Get("/twilio_tokens")
     *
     * @param TwilioVoiceAccount $account
     *
     * @return View
     */
    public function createTwilioClientTokensAction(TwilioVoiceAccount $account)
    {
        $adapter = $this->get('twilio_adapter');
        $person  = $this->getUser();

        $clientTokens = new TwilioClientTokens(
            $adapter->createPhoneToken($account, $person)
        );

        return new View($this->wrap($clientTokens));
    }

    /**
     * @ApiDoc(
     *     description="Prepares outbound phone call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceOutboundCallType"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall"
     * )
     *
     * @Rest\Post("/prepare_outbound_call")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function prepareOutboundCallAction(Request $request)
    {
        $form = $this->createForm(VoiceOutboundCallType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $form->getData();

        $em = $this->getManager();
        $em->persist($phoneCall);
        $em->flush();

        $em->flush();

        return new View($this->wrap($phoneCall));
    }

    /**
     * @Rest\Get("/online_agents")
     *
     * @return View
     */
    public function getOnlineAgentsAction()
    {
        return new View($this->wrap($this->get('dp.voice.worker_activity')->getActiveWorkers()));
    }

    /**
     * @Rest\Post("/client_error")
     *
     * @param Request $request
     *
     * @return View
     */
    public function clientErrorAction(Request $request)
    {
        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[VoiceClient] Got client error, code = %s, message = %s, original_code = %s, original_message = %s',
            $request->get('message'),
            $request->get('code'),
            $request->get('original_code'),
            $request->get('original_message')
        ));

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/performance_logs")
     *
     * @return Response
     */
    public function performanceLogsAction()
    {
        $content = file_get_contents($this->get('deskpro.app_env')->getUserLogsDir().'/voice.log');
        if (!$content) {
            $content = '';
        }

        return new Response($content);
    }
}
