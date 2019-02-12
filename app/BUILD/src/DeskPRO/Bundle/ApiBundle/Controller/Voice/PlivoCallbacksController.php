<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceBlobAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceTextAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\VoiceBundle\Exception\InsufficientBalanceException;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\Exception\UnverifiedException;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use FOS\RestBundle\Controller\Annotations as Rest;
use Plivo\XML\Response as PlivoXML;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PlivoCallbacksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/plivo_callbacks/{account}/{accountAuth}")
 * @ApiUserContext("open")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class PlivoCallbacksController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Triggers when user starts calling (incoming call).",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/answer_user_callback", name="plivo_answer_user_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function answerUserCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $plivoXml = new PlivoXML();

        try {
            $phoneCall = $this->get('dp.voice.callbacks_helper')->createIncomingPhoneCall(
                $request->get('CallUUID'),
                $request->get('From'),
                $request->get('To'),
                $request->request->all()
            );

            $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $plivoXml);
        } catch (OutOfServiceException $e) {
            $plivoXml->addSpeak(sprintf('Thank you for calling, %s', $this->getContainer()->getBrandSetting('core.deskpro_name')), [
                'voice' => 'WOMAN',
            ]);
            $plivoXml->addSpeak('Required phone number is out of service.', [
                'voice' => 'WOMAN',
            ]);
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Triggers when agent starts calling (outgoing call or accepting an incoming call).",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/answer_agent_callback", name="plivo_answer_agent_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function answerAgentCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callId  = $request->get('X-PH-CallId');
        $callSid = $request->get('CallUUID');
        $agentId = $request->get('X-PH-AgentId');
        $details = $request->request->all();

        $plivoXml = new PlivoXML();

        if ($request->request->get('X-PH-Outbound')) {
            try {
                $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
                if (!$phoneCall) {
                    throw $this->createBadRequestException('Phone call not found');
                }

                $this->get('dp.voice.callbacks_helper')->setOutgoingAgentParticipant($callId, $callSid, $agentId, $details);

                // make an outbound call
                $callRequestId = $this->get('plivo_adapter')->callNumber(
                    $phoneCall->getNumber(),
                    $phoneCall->getExternalNumber(),
                    $this->getOutboundCallbackUrl($account, $phoneCall),
                    'POST',
                    $exception
                );

                if ($callRequestId) {
                    // create and join a new conference
                    $plivoXml->addConference($phoneCall->getConferenceName(), [
                        'enterSound'     => false,
                        'callbackUrl'    => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                        'callbackMethod' => 'POST',
                        'record'         => true,
                    ]);
                } else {
                    $errorCodeGen = $this->get('form_error.error_code_generator.api');
                    if ($exception instanceof UnverifiedException) {
                        $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::UNVERIFIED_NUMBER, [], ['call_to']);
                    } elseif ($exception instanceof InsufficientBalanceException) {
                        $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::INSUFFICIENT_BALANCE, [], ['call_to']);
                    } else {
                        $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::VOICE_PERMISSIONS, [], ['call_to']);
                    }

                    $this->get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent.voice.outgoing-provider-error', $errorMessage)
                    );

                    $plivoXml->addHangup();
                }
            } catch (OutOfServiceException $e) {
                $plivoXml->addSpeak('Unable to make a call.', [
                    'voice' => 'WOMAN',
                ]);
            }
        } else {
            try {
                $phoneCall = $this->get('dp.voice.callbacks_helper')->joinIncomingPhoneCall(
                    $callId,
                    $callSid,
                    $agentId,
                    null,
                    $details
                );

                if ($phoneCall) {
                    // join conference
                    /** @var PlivoVoiceAccount $account */
                    $account = $phoneCall->getNumber()->getAccount();
                    $plivoXml->addConference($phoneCall->getConferenceName(), [
                        'enterSound'     => false,
                        'callbackUrl'    => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                        'callbackMethod' => 'POST',
                        'record'         => true,
                    ]);

                    // join user to the conference
                    if ($phoneCall->getParticipants()->count() <= 2) {
                        $this->get('dp.voice.provider_helper')->transferCall(
                            $phoneCall,
                            $this->getUserJoinsConferenceCallbackUrl($account, $phoneCall),
                            'POST'
                        );
                    }
                } else {
                    $plivoXml->addHangup();
                }
            } catch (OutOfServiceException $e) {
                $plivoXml->addSpeak('Unable to answer the call.', [
                    'voice' => 'WOMAN',
                ]);
            }
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Triggers when agent accept or decline a forwarding call.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/answer_forwarding_callback", name="plivo_answer_forwarding_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function answeredForwardingCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callId     = $request->query->get('CallId');
        $agentId    = $request->query->get('AgentId');
        $callSid    = $request->get('CallUUID');
        $callStatus = $request->get('CallStatus');
        $details    = $request->request->all();

        $plivoXml = new PlivoXML();

        if ($callStatus === 'busy') {
            if ($agentId && $callId) {
                $agent     = $this->getManager()->getRepository(Person::class)->find($agentId);
                $phoneCall = $this->getManager()->getRepository(VoicePhoneCall::class)->find($callId);

                if ($agent && $phoneCall) {
                    // reject the call
                    $this->get('dp.voice.callbacks_helper')->rejectIncomingPhoneCall($phoneCall, $agent);
                }
            }
        } elseif ($callStatus === 'in-progress') {
            if (!$callId || !$phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId)) {
                throw $this->createBadRequestException('Phone call not found');
            }
            if (!$agentId || !$agent = $this->get('dp.voice.callbacks_helper')->getAgent($agentId)) {
                throw $this->createBadRequestException('Agent not found');
            }

            $phoneCall->addForwardingSid($agentId, $callSid);

            $em = $this->getManager();
            $em->persist($phoneCall);
            $em->flush();

            $this->get('dp.voice.callbacks_helper')->createOrJoinTicketForIncomingCall($phoneCall, $agent);

            try {
                $phoneCall = $this->get('dp.voice.callbacks_helper')->joinIncomingPhoneCall(
                    $callId,
                    $callSid,
                    $agentId,
                    $agent->getAgentData()->getForwardingNumber(),
                    $details
                );

                if ($phoneCall) {
                    // join conference
                    /** @var PlivoVoiceAccount $account */
                    $account = $phoneCall->getNumber()->getAccount();
                    $plivoXml->addConference($phoneCall->getConferenceName(), [
                        'enterSound'     => false,
                        'callbackUrl'    => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                        'callbackMethod' => 'POST',
                        'record'         => true,
                    ]);

                    // join user to the conference
                    if ($phoneCall->getParticipants()->count() <= 2) {
                        $this->get('dp.voice.provider_helper')->transferCall(
                            $phoneCall,
                            $this->getUserJoinsConferenceCallbackUrl($account, $phoneCall),
                            'POST'
                        );
                    }

                    $this->get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent.voice.call-answered', [
                            'call_id' => $phoneCall->getId(),
                        ])
                    );
                } else {
                    $plivoXml->addHangup();
                }
            } catch (OutOfServiceException $e) {
                $plivoXml->addSpeak('Unable to answer the call.', [
                    'voice' => 'WOMAN',
                ]);
            }
        } elseif ($callStatus === 'completed') {
            $this->get('dp.voice.callbacks_helper')->callHangupByAgent($callSid, $details);
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Triggers when user ends call.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/hangup_user_callback", name="plivo_hangup_user_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function hangupUserCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid    = $request->get('CallUUID');
        $callStatus = $request->get('CallStatus');
        $details    = $request->request->all();

        if ($callStatus === 'completed') {
            $this->get('dp.voice.callbacks_helper')->callHangupByUser($callSid, $details);
        } elseif ($callStatus === 'busy') {
            $this->get('dp.voice.callbacks_helper')->callBusyByUser($callSid, $details);
        }

        // need to end conference
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $callSid,
        ]);
        if ($phoneCall) {
            $this->get('dp.voice.provider_helper')->endConference($phoneCall);
        }

        $plivoXml = new PlivoXML();
        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Triggers when agent ends call.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/hangup_agent_callback", name="plivo_hangup_agent_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function hangupAgentCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid    = $request->get('CallUUID');
        $callStatus = $request->get('CallStatus');
        $details    = $request->request->all();

        if ($callStatus === 'completed') {
            $this->get('dp.voice.callbacks_helper')->callHangupByAgent($callSid, $details);
        }

        $plivoXml = new PlivoXML();

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Recursively triggers to match incoming phone call to a worker",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/call_routing", name="plivo_call_routing_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function callRoutingAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $plivoXml = new PlivoXML();

        $taskId = $request->query->getInt('task');
        $task   = $this->container->get('dp.voice.task_router.storage')->getTask($taskId);
        if ($task) {
            // task router ping timeout
            $queue = $this->container->get('dp.voice.voice_task_helper')->getVoiceQueue($task);
            if ($queue && $queue->getLoopAsset()) {
                $plivoXml->addPlay($this->getHoldMusicUrl($account, $queue->getLoopAsset()));
            } else {
                $plivoXml->addPlay($this->get('dp.voice.assets_helper')->getDefaultRingAssetUrl());
            }

            // phone call's got a conference sid that means an agent accepted the call
            // join user to the conference
            $phoneCall = $this->container->get('dp.voice.voice_task_helper')->getPhoneCall($task);
            if ($phoneCall && $phoneCall->getConferenceSid()) {
                $conference = $this->get('plivo_adapter')->getConference($account, $phoneCall->getConferenceName());
                // unable to get conference or there are no members, e.g. all agents already left the conference
                // just end the call
                if (!$conference || $conference->conferenceMemberCount === 0) {
                    $plivoXml->addHangup();
                }
            } else {
                $hadWorkers = $task->getWorkerIds();

                // evaluate task router
                $this->container->get('dp.voice.task_router')->evaluate();
                // reload task after task router
                $task = $this->container->get('dp.voice.task_router.storage')->getTask($taskId);

                if ($task->isTimeout()) {
                    $plivoXml->addRedirect($this->getVoicemailUrl($account, $this->get('dp.voice.assets_helper')->getVoicemailAsset($taskId)));
                    $this->get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent.voice.reached-voicemail', [
                            'call_id' => $phoneCall->getId(),
                        ])
                    );
                } else {
                    // workers was just found
                    if (!$hadWorkers && $task->getWorkerIds()) {
                        // forwarding calls
                        $workers = $this->container->get('dp.voice.task_router.storage')->getWorkers($task->getWorkerIds());

                        foreach ($workers as $worker) {
                            $agent = $this->getRepository(Person::class)->find($worker->getTypeId());
                            if ($agent && $agent->canForwardCall()) {
                                // make an outbound call
                                $callRequestId = $this->get('plivo_adapter')->callNumber(
                                    $phoneCall->getNumber(),
                                    $agent->getForwardingNumber(),
                                    $this->getAnswerForwardingUrl($account, $phoneCall, $agent),
                                    'POST'
                                );

                                if ($callRequestId) {
                                    $phoneCall->addForwardingRequestId($agent->getId(), $callRequestId);
                                }
                            }

                            $em = $this->getManager();
                            $em->persist($phoneCall);
                            $em->flush();
                        }
                    }

                    $plivoXml->addRedirect($this->getCallRoutingCallbackUrl($account, $task));
                }
            }
        } else {
            $plivoXml->addHangup();
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Conference status callback, triggers when a participant joins or leaves call, accepts call recording.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/{phoneCall}/conference_status_callback", name="plivo_conference_status_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param VoicePhoneCall    $phoneCall
     * @param Request           $request
     *
     * @throws \Exception
     */
    public function conferenceStatusCallbackAction(PlivoVoiceAccount $account, $accountAuth, VoicePhoneCall $phoneCall, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid       = $request->request->get('CallUUID');
        $conferenceSid = $request->request->get('ConferenceUUID');
        $details       = $request->request->all();
        $eventName     = $request->request->get('ConferenceAction');

        if ($eventName === 'enter') {
            $this->get('dp.voice.callbacks_helper')->joinConference($phoneCall, $callSid, $conferenceSid, $details);
        } elseif ($eventName === 'record') {
            $recordUrl      = $request->request->get('RecordUrl');
            $recordDuration = $request->request->get('RecordingDuration');

            // if no duration then it means the record is not downloaded yet
            if ($recordDuration) {
                $this->get('dp.voice.recording_download_helper')->enqueueRecordingDownload($phoneCall, $recordUrl, $recordDuration);
            }
        }

        // send client message for real time ui updates
        // call id could be empty, e.g. for record event
        if ($callSid) {
            $this->get('dp.voice.callbacks_helper')->sendConferenceStatus($phoneCall);
        }
    }

    /**
     * @ApiDoc(
     *     description="Triggers when user presses a dial code in IVR mode.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/auto_attendant_callback/{autoAttendant}", name="plivo_auto_attendant_callback")
     *
     * @param PlivoVoiceAccount  $account
     * @param string             $accountAuth
     * @param VoiceAutoAttendant $autoAttendant
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function autoAttendantCallbackAction(PlivoVoiceAccount $account, $accountAuth, VoiceAutoAttendant $autoAttendant, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $request->request->get('CallUUID'),
        ]);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $plivoXml = new PlivoXML();

        $details     = $request->request->all();
        $enteredCode = $request->request->get('Digits');

        if (is_numeric($enteredCode)) {
            $dialNumber = $autoAttendant->getDialNumber((int) $enteredCode);
            if ($dialNumber) {
                $this->addTargetResponse($phoneCall, $dialNumber->getTarget(), $plivoXml);
                $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantDigit($phoneCall, $dialNumber, $details);
            } else {
                $plivoXml->addSpeak('Required dial number is not supported.', [
                    'voice' => 'WOMAN',
                ]);

                $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $plivoXml);
                $this->get('dp.voice.callbacks_helper')->logPressedUnsupportedAutoAttendantDigit($phoneCall, $details);
            }
        } elseif ($enteredCode === '*') {
            $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $plivoXml);
            $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantRepeatKey($phoneCall, $details);
        } elseif ($enteredCode === '#') {
            $plivoXml
                ->addGetDigits([
                    'numDigits'   => 4,
                    'action'      => $this->getAgentExtensionCallbackUrl($account),
                    'finishOnKey' => 'None',
                ])
                ->addSpeak('Please enter agent extension number', [
                    'voice' => 'WOMAN',
                ])
            ;

            $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantExtensionKey($phoneCall, $details);
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Triggers when user entered an agent extension number.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/agent_extension_callback", name="plivo_agent_extension_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function agentExtensionCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $plivoXml  = new PlivoXML();
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $request->request->get('CallUUID'),
        ]);
        if (!$phoneCall) {
            $plivoXml->addSpeak('Unable to enter an extension number.', [
                'voice' => 'WOMAN',
            ]);
        } else {
            $enteredCode = $request->request->get('Digits');
            $target      = $this->get('dp.voice.callbacks_helper')->getTargetByExtensionNumber($enteredCode);
            if ($target) {
                $this->addTargetResponse($phoneCall, $target, $plivoXml);
            } else {
                $plivoXml
                    ->addGetDigits([
                        'numDigits'   => 4,
                        'action'      => $this->getAgentExtensionCallbackUrl($account),
                        'method'      => 'POST',
                        'finishOnKey' => 'None',
                    ])
                    ->addSpeak(sprintf('Requested agent with %d extension number does not exist. Please enter agent extension number again.', $enteredCode), [
                        'voice' => 'WOMAN',
                    ])
                ;
            }

            // log agent extension event
            $this->get('dp.voice.callbacks_helper')->logEnteredAgentExtension($phoneCall, $enteredCode, $request->request->all());
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Custom hold music",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Get("/hold_music", name="plivo_hold_music")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function holdMusicAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $asset   = null;
        $assetId = $request->query->get('asset');
        if ($assetId) {
            $asset = $this->getRepository(AbstractVoiceAsset::class)->find($assetId);
        }

        $plivoXml = new PlivoXML();
        if ($asset instanceof VoiceTextAsset) {
            $plivoXml->addSpeak($asset->getText(), [
                'loop'  => 0,
                'voice' => 'WOMAN',
            ]);
        } elseif ($asset instanceof AbstractVoiceBlobAsset) {
            $plivoXml->addPlay($asset->getBlob()->getDownloadUrl(true), [
                'loop' => 0,
            ]);
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Voicemail callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     filters={
     *          {"name"="asset", "pattern"="\d", "description"="voice asset id", "dataType"="integer"}
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/voicemail", name="plivo_voicemail")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $asset   = null;
        $assetId = $request->query->get('asset');
        if ($assetId) {
            $asset = $this->getRepository(AbstractVoiceAsset::class)->find($assetId);
        }

        // get voicemail message
        $plivoXml = new PlivoXML();

        if ($asset) {
            $this->playGreetAsset($plivoXml, $asset);
        } else {
            $plivoXml->addSpeak('You have reached voicemail. Please leave a message.', [
                'voice' => 'WOMAN',
            ]);
        }

        $plivoXml->addRecord([
            'action'         => $this->getVoicemailEndUrl($account),
            'method'         => 'POST',
            'callbackUrl'    => $this->getRecordingStatusCallbackUrl($account),
            'callbackMethod' => 'POST',
        ]);

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Voicemail end callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/voicemail_end", name="plivo_voicemail_end")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailEndAction(PlivoVoiceAccount $account, $accountAuth)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $plivoXml = new PlivoXML();
        $plivoXml->addHangup();

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Recording status callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/recording_status_callback", name="plivo_recording_status_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     */
    public function recordingStatusCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $request->request->get('CallUUID'),
        ]);

        if ($phoneCall) {
            $this->get('dp.voice.recording_download_helper')->enqueueRecordingDownload(
                $phoneCall,
                $request->request->get('RecordUrl'),
                $request->request->get('RecordingDuration')
            );
        }
    }

    /**
     * @param VoicePhoneCall      $phoneCall
     * @param AbstractVoiceTarget $target
     * @param PlivoXML            $plivoXml
     */
    private function addTargetResponse(VoicePhoneCall $phoneCall, AbstractVoiceTarget $target, PlivoXML $plivoXml)
    {
        $number  = $phoneCall->getNumber();
        $account = $number->getAccount();

        if (!$account instanceof PlivoVoiceAccount) {
            return;
        }

        if ($target instanceof VoiceQueueTarget) {
            $this->playGreetAsset($plivoXml, $target->getQueue()->getGreetAsset());
        } elseif ($target instanceof VoiceAutoAttendantTarget) {
            $autoAttendant = $target->getAutoAttendant();
            $dialNumbers   = $autoAttendant->getOrderedDialNumbers();

            $gather = $plivoXml->addGetDigits([
                'numDigits'   => 1,
                'action'      => $this->getAutoAttendantCallbackUrl($account, $autoAttendant),
                'method'      => 'POST',
                'timeout'     => 30,
                'finishOnKey' => 'None',
            ]);

            $asset = $autoAttendant->getAudioAsset();

            // no asset or text asset with enabled auto generated option
            if (!$asset || ($asset instanceof VoiceTextAsset && $asset->getAutoGenerated())) {
                $gather->addSpeak('Welcome to '.$this->getContainer()->getBrandSetting('core.deskpro_name'), [
                    'voice' => 'WOMAN',
                ]);
                $gather->addWait([
                    'length' => 2,
                ]);

                foreach ($dialNumbers as $dialNumber) {
                    $targetDetails = $dialNumber->getTarget()->getTargetDetails();

                    $gather->addSpeak(sprintf(
                        'For %s, press %d',
                        $targetDetails['name'], $dialNumber->getDialNum()
                    ), [
                        'voice' => 'WOMAN',
                    ]);
                }

                if ($autoAttendant->getAllowRepeatMenu()) {
                    $gather->addSpeak('To repeat the main menu, press * key', [
                        'voice' => 'WOMAN',
                    ]);
                }
                if ($autoAttendant->getAllowExtension()) {
                    $gather->addSpeak('To enter agent extension number, press # key', [
                        'voice' => 'WOMAN',
                    ]);
                }
            } else {
                $this->playGreetAsset($gather, $asset);
            }
        }

        // enqueue task for task router
        $task = $this->get('dp.voice.callbacks_helper')->createTaskForTarget($phoneCall, $target);
        if ($task) {
            $plivoXml->addRedirect($this->getCallRoutingCallbackUrl($account, $task));
        }
    }

    /**
     * User answered an incoming call.
     *
     * @ApiDoc(
     *     description="User accepted an incoming call.",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/outbound_callback", name="plivo_outbound_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function outgoingUserCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid    = $request->get('CallUUID');
        $callId     = $request->get('callId');
        $callStatus = $request->get('CallStatus');
        $details    = $request->request->all();

        $plivoXml = new PlivoXML();

        if ($callStatus === 'busy') {
            $this->get('dp.voice.callbacks_helper')->callBusyByUser($callSid, $details);
        } elseif ($callStatus === 'in-progress') {
            $this->get('dp.voice.callbacks_helper')->setOutgoingUserParticipant($callId, $callSid);
            $this->get('dp.voice.callbacks_helper')->createTicketForOutgoingPhoneCall($callId);
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);

            $plivoXml->addConference($phoneCall->getConferenceName(), [
                'endConferenceOnExit' => true,
                'enterSound'          => false,
                'callbackUrl'         => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                'callbackMethod'      => 'POST',
                'record'              => true,
            ]);
        } elseif ($callStatus === 'completed') {
            // in case the call was hanged up immediately
            // try to set user participant here as well before hanging up the phone call
            $this->get('dp.voice.callbacks_helper')->setOutgoingUserParticipant($callId, $callSid);
            $this->get('dp.voice.callbacks_helper')->callHangupByUser($callSid, $details);
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="User joins conference callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/user_joins_conference_callback", name="plivo_user_joins_conference_callback")
     *
     * @param PlivoVoiceAccount $account
     * @param string            $accountAuth
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function userJoinsConferenceCallbackAction(PlivoVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $plivoXml = new PlivoXML();
        $callId   = $request->get('callId');
        if ($callId) {
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
            if ($phoneCall) {
                $plivoXml->addConference($phoneCall->getConferenceName(), [
                    'endConferenceOnExit' => true,
                    'callbackUrl'         => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                    'callbackMethod'      => 'POST',
                    'record'              => true,
                ]);
            }
        }

        $response = new Response($plivoXml->toXML());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param Task              $task
     *
     * @return string
     */
    private function getCallRoutingCallbackUrl(PlivoVoiceAccount $account, Task $task)
    {
        return $this->get('router')->generate('plivo_call_routing_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'task'        => $task->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount  $account
     * @param VoiceAutoAttendant $autoAttendant
     *
     * @return string
     */
    private function getAutoAttendantCallbackUrl(PlivoVoiceAccount $account, VoiceAutoAttendant $autoAttendant)
    {
        return $this->get('router')->generate('plivo_auto_attendant_callback', [
            'account'       => $account->getId(),
            'accountAuth'   => $account->getAccountAuth(),
            'autoAttendant' => $autoAttendant->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return string
     */
    private function getAgentExtensionCallbackUrl(PlivoVoiceAccount $account)
    {
        return $this->get('router')->generate('plivo_agent_extension_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount  $account
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getHoldMusicUrl(PlivoVoiceAccount $account, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('plivo_hold_music', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount  $account
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getVoicemailUrl(PlivoVoiceAccount $account, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('plivo_voicemail', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return string
     */
    private function getVoicemailEndUrl(PlivoVoiceAccount $account)
    {
        return $this->get('router')->generate('plivo_voicemail_end', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return string
     */
    private function getRecordingStatusCallbackUrl(PlivoVoiceAccount $account)
    {
        return $this->get('router')->generate('plivo_recording_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param VoicePhoneCall    $phoneCall
     * @param Person            $agent
     *
     * @return string
     */
    private function getAnswerForwardingUrl(PlivoVoiceAccount $account, VoicePhoneCall $phoneCall, Person $agent)
    {
        return $this->get('router')->generate('plivo_answer_forwarding_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'CallId'      => $phoneCall->getId(),
            'AgentId'     => $agent->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param VoicePhoneCall    $phoneCall
     *
     * @return string
     */
    private function getConferenceStatusCallbackUrl(PlivoVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('plivo_conference_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param VoicePhoneCall    $phoneCall
     *
     * @return string
     */
    private function getOutboundCallbackUrl(PlivoVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('plivo_outbound_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param VoicePhoneCall    $phoneCall
     *
     * @return string
     */
    private function getUserJoinsConferenceCallbackUrl(PlivoVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('plivo_user_joins_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param PlivoXML                $plivoXml
     * @param AbstractVoiceAsset|null $asset
     */
    private function playGreetAsset(PlivoXML $plivoXml, AbstractVoiceAsset $asset = null)
    {
        if ($asset instanceof VoiceTextAsset) {
            $pattern = '#({{(?:\s+|)pause(?:\s+|)(?:\d+|)(?:\s+|)}})#';

            $text = $asset->getText();
            $text = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            foreach ($text as $part) {
                if (preg_match($pattern, $part)) {
                    if (preg_match('#\d+#', $part, $m)) {
                        $pause = $m[0];
                    } else {
                        $pause = 2;
                    }

                    $plivoXml->addWait([
                        'length' => $pause,
                    ]);
                } else {
                    $plivoXml->addSpeak($part, [
                        'voice'    => 'WOMAN',
                        'language' => $asset->getLanguage(),
                    ]);
                }
            }
        } elseif ($asset instanceof AbstractVoiceBlobAsset) {
            $plivoXml->addPlay($asset->getBlob()->getDownloadUrl(true));
        }
    }
}
