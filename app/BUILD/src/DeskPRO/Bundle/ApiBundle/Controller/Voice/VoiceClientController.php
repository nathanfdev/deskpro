<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Repository\VoiceAccountRepository;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceOutboundCallType;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioClientTokens;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;

/**
 * Handles client actions.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_client")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceClientController extends AbstractVoiceController
{
    /**
     * @ApiDoc(
     *     description="Returns client voice auth tokens",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioClientTokens"
     * )
     *
     * @Rest\Get("/tokens")
     *
     * @return View
     */
    public function createWorkerTokenAction()
    {
        $adapter = $this->get('twilio_adapter');
        $account = $this->getVoiceAccount();
        $person  = $this->getUser();

        $clientTokens = new TwilioClientTokens(
            $adapter->createWorkerToken($account, $person),
            $adapter->createPhoneToken($account, $person)
        );

        return new View($this->wrap($clientTokens));
    }

    /**
     * @ApiDoc(
     *     description="Returns voice worker activity sids",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities"
     * )
     *
     * @Rest\Get("/activities")
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getActivitiesAction()
    {
        $adapter = $this->get('twilio_adapter');
        $account = $this->getVoiceAccount();

        try {
            return new View($this->wrap($adapter->getActivities($account)));
        } catch (RestException $e) {
            if (in_array($e->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_NOT_FOUND])) {
                return new TwilioActivities('', '', '', '', '');
            }

            throw $e;
        }
    }

    /**
     * @ApiDoc(
     *     description="Declines and ignores incoming phone call",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/reject_call/{taskSid}")
     *
     * @param string $taskSid
     *
     * @return View
     */
    public function rejectCallAction($taskSid)
    {
        $phoneCall = $this->getManager()->getRepository(VoicePhoneCall::class)->findOneBy([
            'taskSid' => $taskSid,
        ]);

        if ($phoneCall) {
            $this->rejectIncomingPhoneCall($phoneCall, $this->getUser());
            $this->cancelForwardingCalls($phoneCall, $this->getUser());
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Prepares outbound phone call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceOutboundCallType"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall"
     * )
     *
     * @Rest\Post("/prepare_outbound_call")
     *
     * @param Request $request
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

        $phoneCall = $form->getData();

        $em = $this->getManager();
        $em->persist($phoneCall);
        $em->flush();

        return new View($this->wrap($phoneCall));
    }

    /**
     * @return VoiceAccount
     */
    private function getVoiceAccount()
    {
        /** @var VoiceAccountRepository $voiceAccountRepo */
        $voiceAccountRepo = $this->getRepository(VoiceAccount::class);
        $voiceAccount     = $voiceAccountRepo->getVoiceAccount();

        if (!$voiceAccount) {
            throw $this->createBadRequestException('Voice account not found');
        }

        return $voiceAccount;
    }
}
