<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceBlobAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceTextAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use DeskPRO\Bundle\VoiceBundle\Twilio\Twiml;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Exceptions\RestException;

/**
 * Class TwilioCallbacksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/twilio_callbacks/{account}/{accountAuth}")
 * @ApiUserContext("open")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class TwilioCallbacksController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Handle incoming phone call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Get("/phone_number_callback", name="twilio_phone_number_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function phoneNumberCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        if ($request->query->get('Outbound')) {
            // outbound call
            return $this->phoneNumberAgentOutgoingCallback($account, $request);
        } else {
            // incoming call
            if (TwilioAdapter::isWorkerContactUrl($request->query->get('From'))) {
                // agent connection
                return $this->phoneNumberAgentIncomingCallback($request);
            } else {
                // user connection
                return $this->phoneNumberUserIncomingCallback($request);
            }
        }
    }

    /**
     * @ApiDoc(
     *     description="Handle incoming phone call changed status",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/phone_number_status_callback", name="twilio_phone_number_status_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     */
    public function phoneNumberStatusCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getManager();

        $details    = $request->request->all();
        $callSid    = $request->request->get('CallSid');
        $callStatus = $request->request->get('CallStatus');

        if (!$callSid) {
            return;
        }

        if ($callStatus === 'completed') {
            // log call participants
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($participant) {
                if ($participant instanceof VoicePhoneCallParticipantUser) {
                    $this->get('dp.voice.callbacks_helper')->callHangupByUser($callSid, $details);
                } else {
                    $this->get('dp.voice.callbacks_helper')->callHangupByAgent($callSid, $details);
                }

                $this->get('dp.voice.provider_helper')->tryEndConference($participant->getPhoneCall());
            }
        } elseif ($callStatus === 'busy') {
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($participant instanceof VoicePhoneCallParticipantUser) {
                $this->get('dp.voice.callbacks_helper')->callBusyByUser($callSid, $details);
                $this->get('dp.voice.provider_helper')->tryEndConference($participant->getPhoneCall());
            } else {
                // no participant means agent declined forwarding call
                $agentId = $request->query->get('agentId');
                $callId  = $request->query->get('callId');
                if (!$agentId || !$callId) {
                    return;
                }

                $agent     = $em->getRepository(Person::class)->find($agentId);
                $phoneCall = $em->getRepository(VoicePhoneCall::class)->find($callId);
                if (!$agent || !$phoneCall) {
                    return;
                }

                // reject the call
                $this->get('dp.voice.callbacks_helper')->rejectIncomingPhoneCall($phoneCall, $agent);
            }
        }
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
     * @Rest\Post("/call_routing", name="twilio_call_routing_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function callRoutingAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $twiml = new Twiml();

        $taskId = $request->query->getInt('task');
        $task   = $this->container->get('dp.voice.task_router.storage')->getTask($taskId);
        if ($task) {
            // task router ping timeout
            $queue = $this->container->get('dp.voice.voice_task_helper')->getVoiceQueue($task);
            if ($queue && $queue->getLoopAsset()) {
                $twiml->play($this->getHoldMusicUrl($account, $queue->getLoopAsset()));
            } else {
                $twiml->play($this->get('dp.voice.assets_helper')->getDefaultRingAssetUrl());
            }

            // phone call's got a conference sid that means an agent accepted the call
            // join user to the conference
            $phoneCall = $this->container->get('dp.voice.voice_task_helper')->getPhoneCall($task);
            if ($phoneCall && $phoneCall->getConferenceSid()) {
                $conference = $this->get('twilio_adapter')->getConference($account, $phoneCall->getConferenceSid());
                if (!$conference || $conference->status === 'completed') {
                    $twiml->hangup();
                }
            } else {
                $hadWorkers = $task->getWorkerIds();

                // evaluate task router
                $this->container->get('dp.voice.task_router')->evaluate();
                // reload task after task router
                $task = $this->container->get('dp.voice.task_router.storage')->getTask($taskId);

                if ($task->isTimeout()) {
                    $twiml->redirect($this->getVoicemailUrl($account, $this->get('dp.voice.assets_helper')->getVoicemailAsset($taskId)));
                } else {
                    // workers was just found
                    if (!$hadWorkers && $task->getWorkerIds()) {
                        // forwarding calls
                        $workers = $this->container->get('dp.voice.task_router.storage')->getWorkers($task->getWorkerIds());

                        foreach ($workers as $worker) {
                            $agent = $this->getRepository(Person::class)->find($worker->getTypeId());
                            if ($agent && $agent->canForwardCall()) {
                                // make an outbound call
                                $call = $this->get('twilio_adapter')->callNumber($phoneCall->getNumber(), $agent->getForwardingNumber(), [
                                    'url'                  => $this->getAnswerForwardingUrl($account, $phoneCall, $agent),
                                    'method'               => 'POST',
                                    'statusCallback'       => $this->getPhoneNumberStatusCallbackUrl($account, $phoneCall, $agent),
                                    'statusCallbackMethod' => 'POST',
                                ]);

                                $phoneCall->addForwardingSid($agent->getId(), $call->sid);

                                $em = $this->getManager();
                                $em->persist($phoneCall);
                                $em->flush();
                            }
                        }
                    }

                    $twiml->redirect($this->getCallRoutingCallbackUrl($account, $task));
                }
            }
        } else {
            $twiml->hangup();
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Conference status callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/{phoneCall}/conference_status_callback", name="twilio_conference_status_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param VoicePhoneCall     $phoneCall
     * @param Request            $request
     *
     * @throws \Exception
     */
    public function conferenceStatusCallbackAction(TwilioVoiceAccount $account, $accountAuth, VoicePhoneCall $phoneCall, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $parameters    = $request->request;
        $callSid       = $parameters->get('CallSid');
        $conferenceSid = $parameters->get('ConferenceSid');
        $eventName     = $parameters->get('StatusCallbackEvent');
        $details       = $request->request->all();

        // we set conference sid on first user participant join
        // otherwise we should have the phone call tied to voice model
        if (!$phoneCall && $eventName !== 'participant-join') {
            throw $this->createBadRequestException('Phone call not found');
        }

        // handle conference events
        if ($eventName === 'conference-start') {
            $this->get('dp.voice.callbacks_helper')->logConferenceStart($phoneCall, $details);
        } elseif ($eventName === 'participant-join') {
            $this->get('dp.voice.callbacks_helper')->joinConference($phoneCall, $callSid, $conferenceSid, $details);
        }

        // send client message
        // for real time ui updates
        $this->get('dp.voice.callbacks_helper')->sendConferenceStatus($phoneCall);
    }

    /**
     * @ApiDoc(
     *     description="Auto attendant callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/auto_attendant_callback/{autoAttendant}", name="twilio_auto_attendant_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param VoiceAutoAttendant $autoAttendant
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function autoAttendantCallbackAction(TwilioVoiceAccount $account, $accountAuth, VoiceAutoAttendant $autoAttendant, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $request->request->get('CallSid'),
        ]);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $twiml = new Twiml();

        $details     = $request->request->all();
        $enteredCode = $request->request->get('Digits');

        if (is_numeric($enteredCode)) {
            $dialNumber = $autoAttendant->getDialNumber((int) $enteredCode);
            if ($dialNumber) {
                $this->addTargetResponse($phoneCall, $dialNumber->getTarget(), $twiml);
                $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantDigit($phoneCall, $dialNumber, $details);
            } else {
                $twiml->say('Required dial number is not supported.', [
                    'voice' => 'alice',
                ]);

                $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $twiml);
                $this->get('dp.voice.callbacks_helper')->logPressedUnsupportedAutoAttendantDigit($phoneCall, $details);
            }
        } elseif ($enteredCode === '*') {
            $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $twiml);
            $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantRepeatKey($phoneCall, $details);
        } elseif ($enteredCode === '#') {
            $twiml
                ->gather([
                    'numDigits' => 4,
                    'action'    => $this->getAgentExtensionCallbackUrl($account),
                ])
                ->say('Please enter agent extension number', [
                    'voice' => 'alice',
                ])
            ;

            $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantExtensionKey($phoneCall, $details);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Agent extension callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/agent_extension_callback", name="twilio_agent_extension_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function agentExtensionCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $twiml     = new Twiml();
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $request->request->get('CallSid'),
        ]);
        if (!$phoneCall) {
            $twiml->say('Unable to enter an extension number.', [
                'voice' => 'alice',
            ]);
        } else {
            $enteredCode = $request->request->get('Digits');
            $target      = $this->get('dp.voice.callbacks_helper')->getTargetByExtensionNumber($enteredCode);
            if ($target) {
                $this->addTargetResponse($phoneCall, $target, $twiml);
            } else {
                $twiml
                    ->gather([
                        'numDigits' => 4,
                        'action'    => $this->getAgentExtensionCallbackUrl($account),
                    ])
                    ->say(sprintf('Requested agent with %d extension number does not exist. Please enter agent extension number again.', $enteredCode), [
                        'voice' => 'alice',
                    ])
                ;
            }

            // log agent extension event
            $this->get('dp.voice.callbacks_helper')->logEnteredAgentExtension($phoneCall, $enteredCode, $request->request->all());
        }

        $response = new Response($twiml);
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
     * @Rest\Post("/recording_status_callback", name="twilio_recording_status_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     */
    public function recordingStatusCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $source    = $request->request->get('RecordingSource');
        $phoneCall = null;
        if ($source === 'RecordVerb') {
            // voicemail record
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
                'callSid' => $request->request->get('CallSid'),
            ]);
        } elseif ($source === 'Conference') {
            // phone call record
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
                'conferenceSid' => $request->request->get('ConferenceSid'),
            ]);
        }
        if ($phoneCall) {
            $this->get('dp.voice.recording_download_helper')->enqueueRecordingDownload(
                $phoneCall,
                $request->request->get('RecordingUrl'),
                $request->request->get('RecordingDuration')
            );
        }
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
     * @Rest\Post("/voicemail", name="twilio_voicemail")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
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
        $twiml = new Twiml();

        if ($asset) {
            $this->playGreetAsset($twiml, $asset);
        } else {
            $twiml->say('You have reached voicemail. Please leave a message.', [
                'voice' => 'alice',
            ]);
        }

        $twiml->record([
            'action'                        => $this->getVoicemailEndUrl($account),
            'method'                        => 'POST',
            'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account),
            'recordingStatusCallbackMethod' => 'POST',
        ]);

        $response = new Response($twiml);
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
     * @Rest\Post("/voicemail_end", name="twilio_voicemail_end")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailEndAction(TwilioVoiceAccount $account, $accountAuth)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $twiml = new Twiml();
        $twiml->hangup();

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * User answered an incoming call.
     *
     * @ApiDoc(
     *     description="Outgoing callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/outbound_callback", name="twilio_outbound_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function outgoingCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callId = $request->query->get('callId');

        $this->get('dp.voice.callbacks_helper')->createTicketForOutgoingPhoneCall($callId);
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);

        $twiml = new Twiml();
        $twiml->dial()->conference($phoneCall->getConferenceName(), [
            'endConferenceOnExit'           => true,
            'statusCallback'                => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
            'statusCallbackMethod'          => 'POST',
            'statusCallbackEvent'           => 'join leave start end mute hold',
            'record'                        => 'record-from-start',
            'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account),
            'recordingStatusCallbackMethod' => 'POST',
        ]);

        $response = new Response($twiml);
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
     * @Rest\Get("/hold_music", name="twilio_hold_music")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function holdMusicAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $asset   = null;
        $assetId = $request->query->get('asset');
        if ($assetId) {
            $asset = $this->getRepository(AbstractVoiceAsset::class)->find($assetId);
        }

        $twiml = new Twiml();
        if ($asset instanceof VoiceTextAsset) {
            $twiml->say($asset->getText(), [
                'loop'  => 0,
                'voice' => 'alice',
            ]);
        } elseif ($asset instanceof AbstractVoiceBlobAsset) {
            $twiml->play($asset->getBlob()->getDownloadUrl(true), [
                'loop' => 0,
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Rest\Post("/answer_forwarding_callback", name="twilio_answer_forwarding_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function answeredForwardingCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callId  = $request->query->get('CallId');
        $agentId = $request->query->get('AgentId');

        if (!$callId || !$phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId)) {
            throw $this->createBadRequestException('Phone call not found');
        }
        if (!$agentId || !$agent = $this->get('dp.voice.callbacks_helper')->getAgent($agentId)) {
            throw $this->createBadRequestException('Agent not found');
        }

        $this->get('dp.voice.callbacks_helper')->createOrJoinTicketForIncomingCall($phoneCall, $agent);

        return $this->phoneNumberAgentIncomingCallback($request);
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    private function phoneNumberAgentIncomingCallback(Request $request)
    {
        $twiml = new Twiml();

        try {
            $phoneCall = $this->get('dp.voice.callbacks_helper')->joinIncomingPhoneCall(
                $request->query->get('CallId'),
                $request->get('CallSid'),
                $request->query->get('AgentId'),
                $request->get('To'),
                $request->query->all()
            );

            if ($phoneCall) {
                /** @var TwilioVoiceAccount $account */
                $account = $phoneCall->getNumber()->getAccount();
                $twiml->dial()->conference($phoneCall->getConferenceName(), [
                    'beep'                          => false,
                    'waitUrl'                       => '',
                    'statusCallback'                => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                    'statusCallbackMethod'          => 'POST',
                    'statusCallbackEvent'           => 'join leave start end mute hold',
                    'record'                        => 'record-from-start',
                    'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account),
                    'recordingStatusCallbackMethod' => 'POST',
                ]);

                // join user to the conference
                $this->get('dp.voice.provider_helper')->joinUserToConference(
                    $phoneCall,
                    $this->getUserJoinsConferenceCallbackUrl($account, $phoneCall),
                    'POST'
                );
            } else {
                $twiml->hangup();
            }
        } catch (OutOfServiceException $e) {
            $twiml->say('Unable to answer the call.', [
                'voice' => 'alice',
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * User makes an incoming call.
     * Callback after Twilio.connect.
     *
     * @param Request $request
     *
     * @return Response
     */
    private function phoneNumberUserIncomingCallback(Request $request)
    {
        $twiml = new Twiml();

        try {
            $phoneCall = $this->get('dp.voice.callbacks_helper')->createIncomingPhoneCall(
                $request->get('CallSid'),
                $request->get('From'),
                $request->get('To'),
                $request->query->all()
            );

            $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $twiml);
        } catch (OutOfServiceException $e) {
            $twiml->say(sprintf('Thank you for calling, %s', $this->getContainer()->getBrandSetting('core.deskpro_name')), [
                'voice' => 'alice',
            ]);
            $twiml->say('Required phone number is out of service.', [
                'voice' => 'alice',
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * Agent makes an outgoing call.
     * Callback after Twilio.connect.
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    private function phoneNumberAgentOutgoingCallback(TwilioVoiceAccount $account, Request $request)
    {
        $callId    = $request->query->get('CallId');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $em = $this->getManager();
        $this->get('dp.voice.callbacks_helper')->setOutgoingAgentParticipant(
            $callId,
            $request->query->get('CallSid'),
            $request->query->get('AgentId'),
            $request->query->all()
        );

        // create twilio new conference response
        $twiml = new Twiml();

        try {
            // make an outbound call
            $call = $this->get('twilio_adapter')->callNumber($phoneCall->getNumber(), $phoneCall->getExternalNumber(), [
                'url'                  => $this->getOutboundCallbackUrl($account, $phoneCall),
                'method'               => 'POST',
                'statusCallback'       => $this->getPhoneNumberStatusCallbackUrl($account, $phoneCall),
                'statusCallbackMethod' => 'POST',
            ]);

            $this->get('dp.voice.callbacks_helper')->setOutgoingUserParticipant($callId, $call->sid);

            // create and join a new conference
            $dial = $twiml->dial(['callerId' => $request->query->get('From')]);
            $dial->conference($phoneCall->getConferenceName(), [
                'waitUrl'                       => '',
                'statusCallback'                => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                'statusCallbackMethod'          => 'POST',
                'statusCallbackEvent'           => 'join leave start end mute hold',
                'record'                        => 'record-from-start',
                'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account),
                'recordingStatusCallbackMethod' => 'POST',
            ]);
        } catch (RestException $e) {
            if ($e->getStatusCode() === 400) {
                $twiml->say(
                    'Unable to make a call to this number.
                     Please check your international permissions to make sure you can call to this country.',
                    [
                        'voice' => 'alice',
                    ]
                );
                $twiml->hangup();

                // mark phone call as ended
                $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
                $em->persist($phoneCall);
                $em->flush();
            }
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param VoicePhoneCall      $phoneCall
     * @param AbstractVoiceTarget $target
     * @param Twiml               $twiml
     *
     * @throws \Exception
     */
    private function addTargetResponse(VoicePhoneCall $phoneCall, AbstractVoiceTarget $target, Twiml $twiml)
    {
        $number  = $phoneCall->getNumber();
        $account = $number->getAccount();

        if (!$account instanceof TwilioVoiceAccount) {
            return;
        }

        if ($target instanceof VoiceQueueTarget) {
            $this->playGreetAsset($twiml, $target->getQueue()->getGreetAsset());
        } elseif ($target instanceof VoiceAutoAttendantTarget) {
            $autoAttendant = $target->getAutoAttendant();
            $dialNumbers   = $autoAttendant->getOrderedDialNumbers();

            $gather = $twiml->gather([
                'numDigits'   => 1,
                'action'      => $this->getAutoAttendantCallbackUrl($account, $autoAttendant),
                'finishOnKey' => '',
                'timeout'     => 30,
            ]);

            $asset = $autoAttendant->getAudioAsset();

            // no asset or text asset with enabled auto generated option
            if (!$asset || ($asset instanceof VoiceTextAsset && $asset->getAutoGenerated())) {
                $gather->say('Welcome to '.$this->getContainer()->getBrandSetting('core.deskpro_name'), [
                    'voice' => 'alice',
                ]);
                $gather->pause([
                    'length' => 2,
                ]);

                foreach ($dialNumbers as $dialNumber) {
                    $targetDetails = $dialNumber->getTarget()->getTargetDetails();

                    $gather->say(sprintf(
                        'For call %s, press %d',
                        $targetDetails['name'], $dialNumber->getDialNum()
                    ), [
                        'voice' => 'alice',
                    ]);
                }

                if ($autoAttendant->getAllowRepeatMenu()) {
                    $gather->say('To repeat the main menu, press * key', [
                        'voice' => 'alice',
                    ]);
                }
                if ($autoAttendant->getAllowExtension()) {
                    $gather->say('To enter agent extension number, press # key', [
                        'voice' => 'alice',
                    ]);
                }
            } else {
                $this->playGreetAsset($gather, $asset);
            }
        }

        // enqueue task for task router
        $task = $this->get('dp.voice.callbacks_helper')->createTaskForTarget($phoneCall, $target);
        if ($task) {
            $twiml->redirect($this->getCallRoutingCallbackUrl($account, $task));
        }
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
     * @Rest\Post("/user_joins_conference_callback", name="twilio_user_joins_conference_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param string             $accountAuth
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function userJoinsConferenceCallbackAction(TwilioVoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $twiml  = new Twiml();
        $callId = $request->get('callId');
        if ($callId) {
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
            if ($phoneCall) {
                $twiml->dial()->conference($phoneCall->getConferenceName(), [
                    'endConferenceOnExit' => true,
                ]);
            }
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param Task               $task
     *
     * @return string
     */
    private function getCallRoutingCallbackUrl(TwilioVoiceAccount $account, Task $task)
    {
        return $this->get('router')->generate('twilio_call_routing_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'task'        => $task->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getConferenceStatusCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_conference_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoiceAutoAttendant $autoAttendant
     *
     * @return string
     */
    private function getAutoAttendantCallbackUrl(TwilioVoiceAccount $account, VoiceAutoAttendant $autoAttendant)
    {
        return $this->get('router')->generate('twilio_auto_attendant_callback', [
            'account'       => $account->getId(),
            'accountAuth'   => $account->getAccountAuth(),
            'autoAttendant' => $autoAttendant->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getAgentExtensionCallbackUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_agent_extension_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getRecordingStatusCallbackUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_recording_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getVoicemailUrl(TwilioVoiceAccount $account, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('twilio_voicemail', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getVoicemailEndUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_voicemail_end', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getHoldMusicUrl(TwilioVoiceAccount $account, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('twilio_hold_music', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getOutboundCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_outbound_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     * @param Person             $agent
     *
     * @return string
     */
    private function getPhoneNumberStatusCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall, Person $agent = null)
    {
        return $this->get('router')->generate('twilio_phone_number_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
            'agentId'     => $agent ? $agent->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     * @param Person             $agent
     *
     * @return string
     */
    private function getAnswerForwardingUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall, Person $agent)
    {
        return $this->get('router')->generate('twilio_answer_forwarding_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'CallId'      => $phoneCall->getId(),
            'AgentId'     => $agent->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getUserJoinsConferenceCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_user_joins_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param Twiml                   $twiml
     * @param AbstractVoiceAsset|null $asset
     */
    private function playGreetAsset(Twiml $twiml, AbstractVoiceAsset $asset = null)
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

                    $twiml->pause([
                        'length' => $pause,
                    ]);
                } else {
                    $twiml->say($part, [
                        'voice'    => 'alice',
                        'language' => $asset->getLanguage(),
                    ]);
                }
            }
        } elseif ($asset instanceof AbstractVoiceBlobAsset) {
            $twiml->play($asset->getBlob()->getDownloadUrl(true));
        }
    }
}
