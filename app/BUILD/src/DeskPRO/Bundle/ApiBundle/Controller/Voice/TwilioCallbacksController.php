<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Job;
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
use DeskPRO\Bundle\AppBundle\Entity\VoiceMissedAgentCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Entity\VoiceRecording;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\VoiceBundle\Exception\BlacklistException;
use DeskPRO\Bundle\VoiceBundle\Exception\InsufficientBalanceException;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\JobQueue\Processor\LoadTwilioPriceProcessor;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;
use DeskPRO\Bundle\VoiceBundle\Twilio\Twiml;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TwilioCallbacksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/twilio_callbacks/{account}/{accountAuth}")
 * @ParamConverter(name="account", converter="twilio_voice_account_sid", options={"tokenParam": "accountAuth"})
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
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function phoneNumberCallbackAction(TwilioVoiceAccount $account, Request $request)
    {
        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf('[TwilioCallbacks] Begin phone number callback, uuid = %s', $request->get('CallSid')));

        if ($request->query->get('Outbound')) {
            // outbound call
            return $this->phoneNumberAgentOutgoingCallback($account, $request);
        } elseif ($request->query->get('Conference') === 'true') {
            return $this->phoneNumberAgentConferenceCallback($account, $request);
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
     * @param Request            $request
     *
     * @throws \Exception
     */
    public function phoneNumberStatusCallbackAction(TwilioVoiceAccount $account, Request $request)
    {
        $em = $this->getManager();

        $details    = $request->request->all();
        $callSid    = $request->request->get('CallSid');
        $callStatus = $request->request->get('CallStatus');

        if (!$callSid) {
            return;
        }

        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf('[TwilioCallbacks] Begin phone number status callback, uuid = %s, call_status = %s', $callSid, $callStatus));

        if ($callStatus === 'completed') {
            // log call participants
            /** @var AbstractVoicePhoneCallParticipant $participant */
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($participant) {
                $lock = $this->get('dp.voice.phone_lock_helper')->createPhoneLock($participant->getPhoneCall()->getId());
                $lock->acquire(true);
                $logger->info(sprintf('[TwilioCallbacks] Lock phone call, uuid = %s', $callSid));

                try {
                    $phoneCall = $participant->getPhoneCall();

                    // get call price
                    // store call price
                    $this->get('job.queue')->addJob(new Job(LoadTwilioPriceProcessor::JOB_TYPE, [
                        'call_id'    => $phoneCall->getId(),
                        'call_sid'   => $callSid,
                        'account_id' => $account->getId(),
                    ]));

                    if ($participant instanceof VoicePhoneCallParticipantUser) {
                        $this->get('dp.voice.callbacks_helper')->callHangupByUser($callSid, $details);
                        $logger->info(sprintf(
                            '[TwilioCallbacks] Hangup call by user, call_id = %s, uuid = %s',
                            $phoneCall->getId(), $callSid
                        ));
                    } else {
                        $this->get('dp.voice.callbacks_helper')->callHangupByAgent($callSid, $details);
                        $logger->info(sprintf(
                            '[TwilioCallbacks] Hangup call by agent, call_id = %s, uuid = %s',
                            $phoneCall->getId(), $callSid
                        ));

                        if (count($phoneCall->getActiveParticipants()) === 2) {
                            // conference is ended
                            // connect user and agent directly
                            $voiceProviderHelper = $this->get('dp.voice.provider_helper');

                            if ($phoneCall->enqueuedAsAgent()) {
                                $voiceProviderHelper->transferParticipant(
                                    $phoneCall->getActiveAgentParticipant(),
                                    $this->getCallRoutingCallbackUrl($account, $phoneCall),
                                    'POST'
                                );

                                $voiceProviderHelper->transferParticipant(
                                    $phoneCall->getUserParticipant(),
                                    $this->acceptDirectCallCallbackUrl($account, $phoneCall),
                                    'POST'
                                );
                            } else {
                                $voiceProviderHelper->transferParticipant(
                                    $phoneCall->getUserParticipant(),
                                    $this->getCallRoutingCallbackUrl($account, $phoneCall),
                                    'POST'
                                );

                                $voiceProviderHelper->transferParticipant(
                                    $phoneCall->getActiveAgentParticipant(),
                                    $this->acceptDirectCallCallbackUrl($account, $phoneCall),
                                    'POST'
                                );
                            }

                            // we transform call back to direct mode
                            // remove conference sid
                            $phoneCall->setConferenceSid(null);
                            $em->flush();
                        }
                    }

                    // send client message
                    // for real time ui updates
                    $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);
                } finally {
                    $lock->release();
                    $logger->info(sprintf('[TwilioCallbacks] Unlock phone call, uuid = %s', $callSid));
                }
            }
        } elseif ($callStatus === 'busy') {
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($participant instanceof VoicePhoneCallParticipantUser) {
                $this->get('dp.voice.callbacks_helper')->callBusyByUser($callSid, $details);
            } else {
                // no participant means agent declined forwarding call
                $agentId = $request->query->get('agentId');
                $callId  = $request->query->get('callId');
                if (!$agentId || !$callId) {
                    return;
                }

                /** @var Person $agent */
                /** @var VoicePhoneCall $phoneCall */
                $agent     = $em->getRepository(Person::class)->find($agentId);
                $phoneCall = $em->getRepository(VoicePhoneCall::class)->find($callId);
                if (!$agent || !$phoneCall) {
                    return;
                }

                // reject the call
                $this->get('dp.voice.callbacks_helper')->rejectIncomingPhoneCall($phoneCall, $agent);

                // send client message
                // for real time ui updates
                $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);
            }
        } elseif ($callStatus === 'failed') {
            $this->get('dp.voice.callbacks_helper')->callFailed($callSid, $details);
        }
    }

    /**
     * @Rest\Post("/{phoneCall}/user_ring_callback", name="twilio_user_ring_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function userRingCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        $twiml = new Twiml();
        $task  = $this->container->get('dp.voice.task_router.storage')->getTask($phoneCall->getTaskSid());
        $queue = $this->container->get('dp.voice.voice_task_helper')->getVoiceQueue($task);
        if ($queue && $queue->getLoopAsset()) {
            $twiml->redirect($this->getHoldMusicUrl($account, $queue->getLoopAsset()));
        } else {
            $twiml->play($this->get('dp.voice.assets_helper')->getDefaultRingAssetUrl(), [
                'loop' => 0,
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Rest\Post("/{phoneCall}/agent_wait_callback", name="twilio_agent_wait_callback")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function agentWaitCallbackAction(VoicePhoneCall $phoneCall)
    {
        $task  = $this->container->get('dp.voice.task_router.storage')->getTask($phoneCall->getTaskSid());
        $twiml = new Twiml();

        $now        = new \DateTime();
        $dateExpire = $this->get('dp.voice.task_router')->getDateExpire($task);
        $timeout    = $dateExpire->getTimestamp() - $now->getTimestamp();

        $twiml->play($this->get('dp.voice.assets_helper')->getDefaultRingAssetUrl(), [
            'timeout' => $timeout > 0 ? $timeout : 0,
            'loop'    => 0,
        ]);

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
     * @param VoicePhoneCall $phoneCall
     * @param Request        $request
     *
     * @throws \Exception
     */
    public function conferenceStatusCallbackAction(VoicePhoneCall $phoneCall, Request $request)
    {
        $parameters    = $request->request;
        $callSid       = $parameters->get('CallSid');
        $conferenceSid = $parameters->get('ConferenceSid');
        $eventName     = $parameters->get('StatusCallbackEvent');

        $lock = $this->get('dp.voice.phone_lock_helper')->createPhoneLock($phoneCall->getId());
        $lock->acquire(true);

        try {
            $this->getManager()->refresh($phoneCall);

            $logger = $this->get('dp.voice.logger');
            $logger->info(sprintf(
                '[TwilioCallbacks] Begin conference status callback, call_id = %s, uuid = %s',
                $phoneCall->getId(), $callSid
            ));

            if ($phoneCall->isEnded()) {
                $logger->info(sprintf(
                    '[TwilioCallbacks] Call is ended, end conference, call_id = %s, uuid = %s',
                    $phoneCall->getId(), $callSid
                ));

                $phoneCall->setConferenceSid($conferenceSid);
                $this->get('dp.voice.provider_helper')->endCall($phoneCall);
            } else {
                // handle conference events
                if ($eventName === 'participant-join') {
                    $this->get('dp.voice.callbacks_helper')->joinConference($phoneCall, $callSid, $conferenceSid);
                    $logger->info(sprintf(
                        '[TwilioCallbacks] Join conference participant, call_id = %s, uuid = %s',
                        $phoneCall->getId(), $callSid
                    ));
                }

                // send client message
                // for real time ui updates
                $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);
            }
        } finally {
            $lock->release();
        }
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
     * @param VoiceAutoAttendant $autoAttendant
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function autoAttendantCallbackAction(TwilioVoiceAccount $account, VoiceAutoAttendant $autoAttendant, Request $request)
    {
        /** @var VoicePhoneCall $phoneCall */
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
                    'numDigits'           => 4,
                    'action'              => $this->getAgentExtensionCallbackUrl($account),
                    'actionOnEmptyResult' => true,
                ])
                ->say('Please enter agent extension number', [
                    'voice' => 'alice',
                ])
            ;

            $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantExtensionKey($phoneCall, $details);
        } elseif (preg_match('/^#.+/', $enteredCode)) {
            $extension = preg_replace('/^#/', '', $enteredCode);
            $target    = $this->get('dp.voice.callbacks_helper')->getTargetByExtensionNumber($extension);
            if ($target) {
                $this->addTargetResponse($phoneCall, $target, $twiml);
            } else {
                $twiml
                    ->gather([
                        'numDigits'           => 4,
                        'action'              => $this->getAgentExtensionCallbackUrl($account),
                        'actionOnEmptyResult' => true,
                    ])
                    ->say(sprintf('Requested agent with %s extension number does not exist. Please enter agent extension number again.', $enteredCode), [
                        'voice' => 'alice',
                    ])
                ;
            }

            // log agent extension event
            $this->get('dp.voice.callbacks_helper')->logPressedAutoAttendantExtensionKey($phoneCall, $details);
            $this->get('dp.voice.callbacks_helper')->logEnteredAgentExtension($phoneCall, $extension, $details);
        } else {
            $target = new VoiceAutoAttendantTarget();
            $target->setAutoAttendant($autoAttendant);

            $this->addTargetResponse($phoneCall, $target, $twiml);
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
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function agentExtensionCallbackAction(TwilioVoiceAccount $account, Request $request)
    {
        $twiml = new Twiml();

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $request->request->get('CallSid'),
        ]);
        if (!$phoneCall) {
            $twiml->say('Unable to enter an extension number.', [
                'voice' => 'alice',
            ]);
        } else {
            $enteredCode = $request->request->get('Digits');
            if ($enteredCode) {
                $target = $this->get('dp.voice.callbacks_helper')->getTargetByExtensionNumber($enteredCode);
                if ($target) {
                    $this->addTargetResponse($phoneCall, $target, $twiml);
                } else {
                    $twiml
                        ->gather([
                            'numDigits'           => 4,
                            'action'              => $this->getAgentExtensionCallbackUrl($account),
                            'actionOnEmptyResult' => true,
                        ])
                        ->say(sprintf('Requested agent with %d extension number does not exist. Please enter agent extension number again.', $enteredCode), [
                            'voice' => 'alice',
                        ])
                    ;
                }

                // log agent extension event
                $this->get('dp.voice.callbacks_helper')->logEnteredAgentExtension($phoneCall, $enteredCode, $request->request->all());
            } else {
                $twiml
                    ->gather([
                        'numDigits'           => 4,
                        'action'              => $this->getAgentExtensionCallbackUrl($account),
                        'actionOnEmptyResult' => true,
                    ])
                    ->say('Please enter agent extension number', [
                        'voice' => 'alice',
                    ])
                ;
            }
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
     * @Rest\Post("/{phoneCall}/recording_status_callback", name="twilio_recording_status_callback")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Request        $request
     *
     * @throws \Exception
     */
    public function recordingStatusCallbackAction(VoicePhoneCall $phoneCall, Request $request)
    {
        $this->get('dp.voice.recording_download_helper')->enqueueRecordingDownload(
            $phoneCall,
            $request->request->get('RecordingSid'),
            $request->request->get('RecordingUrl'),
            $request->request->get('RecordingDuration')
        );
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
     * @Rest\Post("/voicemail_recording_status_callback", name="twilio_voicemail_recording_status_callback")
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    public function voicemailRecordingStatusCallbackAction(Request $request)
    {
        $source    = $request->request->get('RecordingSource');
        $phoneCall = null;
        if ($source === 'RecordVerb') {
            // voicemail record
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
                'callSid' => $request->request->get('CallSid'),
            ]);
        }
        if ($phoneCall instanceof VoicePhoneCall) {
            $this->get('dp.voice.recording_download_helper')->enqueueVoicemailRecordingDownload(
                $phoneCall,
                $request->request->get('RecordingSid'),
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
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailAction(TwilioVoiceAccount $account, Request $request)
    {
        $callSid = $request->get('CallSid');
        $twiml   = new Twiml();

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $callSid,
        ]);
        if (!$phoneCall) {
            $twiml->hangup();
        } else {
            $this->get('event_dispatcher')->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent.voice.reached-voicemail', [
                    'call_id' => $phoneCall->getId(),
                ])
            );

            $asset   = null;
            $assetId = $request->query->get('asset');
            if ($assetId) {
                $asset = $this->getRepository(AbstractVoiceAsset::class)->find($assetId);
            }

            // get voicemail message
            if ($asset) {
                $this->playGreetAsset($twiml, $asset);
            } else {
                $twiml->say('You have reached voicemail. Please leave a message.', [
                    'voice' => 'alice',
                ]);
            }

            $twiml->redirect($this->getVoicemailRecordUrl($account));
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Voicemail disabled callback",
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
     * @Rest\Post("/voicemail_disabled", name="twilio_voicemail_disabled")
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailDisabledAction(TwilioVoiceAccount $account, Request $request)
    {
        $callSid = $request->get('CallSid');
        $twiml   = new Twiml();

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'callSid' => $callSid,
        ]);
        if (!$phoneCall) {
            $twiml->hangup();
        } else {
            $this->get('event_dispatcher')->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent.voice.reached-voicemail', [
                    'call_id' => $phoneCall->getId(),
                ])
            );

            $asset   = null;
            $assetId = $request->query->get('asset');
            if ($assetId) {
                $asset = $this->getRepository(AbstractVoiceAsset::class)->find($assetId);
            }

            // get voicemail message
            if ($asset) {
                $this->playGreetAsset($twiml, $asset);
            } else {
                $twiml->say('No one is able to answer the call. Please call back later.', [
                    'voice' => 'alice',
                ]);
            }

            $twiml->hangup();
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Voicemail record callback",
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
     * @Rest\Post("/voicemail_record", name="twilio_voicemail_record")
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailRecordAction(TwilioVoiceAccount $account, Request $request)
    {
        $callSid = $request->get('CallSid');

        $lock = $this->get('dp.voice.phone_lock_helper')->createPhoneLock($callSid);
        $lock->acquire(true);

        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf('[TwilioCallbacks] Lock phone call, uuid = %s', $callSid));

        $twiml = new Twiml();

        try {
            /** @var VoicePhoneCall $phoneCall */
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            $em = $this->getManager();

            // mark the phone call as completed (redirected to voicemail)
            $phoneCall->setStatus(VoicePhoneCall::STATUS_VOICEMAIL);
            $phoneCall->setDateWaiting(new \DateTime());
            $em->persist($phoneCall);
            $em->flush();

            $logger->info(sprintf('[TwilioCallbacks] Changed call status to voicemail, uuid = %s', $callSid));

            $options = [
                'action'                        => $this->getVoicemailEndUrl($account),
                'method'                        => 'POST',
                'recordingStatusCallback'       => $this->getVoicemailRecordingStatusCallbackUrl($account),
                'recordingStatusCallbackMethod' => 'POST',
            ];

            if ($this->container->get('voice_settings_resolver')->isTranscribeVoicemail()) {
                $options = array_merge($options, [
                    'transcribe'         => true,
                    'transcribeCallback' => $this->getTranscribeVoicemailCallbackUrl($account),
                ]);
            }

            $twiml->record($options);
        } finally {
            $lock->release();
            $logger->info(sprintf('[TwilioCallbacks] Unlock phone call, uuid = %s', $callSid));
        }

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
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailEndAction()
    {
        $twiml = new Twiml();
        $twiml->hangup();

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * Agent is calling, user answered the call.
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
     * @Rest\Post("/outbound_callback/{phoneCall}", name="twilio_outbound_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function outgoingCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall, Request $request)
    {
        $this->get('dp.voice.callbacks_helper')->createTicketForOutgoingPhoneCall($phoneCall->getId());

        $twiml = new Twiml();
        $dial  = $twiml->dial([
            'action'                        => $this->getOnDialHangupCallbackUrl($account, $phoneCall),
            'method'                        => 'POST',
            'record'                        => 'record-from-answer-dual',
            'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account, $phoneCall),
            'recordingStatusCallbackMethod' => 'POST',
        ]);

        $dial->queue($phoneCall->getQueueName());
        $this->get('dp.voice.callbacks_helper')->logConferenceStart($phoneCall, $request->request->all());
        $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);

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
     * @Rest\Post("/hold_music", name="twilio_hold_music_post")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function holdMusicAction(Request $request)
    {
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
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function answeredForwardingCallbackAction(TwilioVoiceAccount $account, Request $request)
    {
        try {
            $answeredBy = $request->request->get('AnsweredBy');
            if (in_array($answeredBy, ['machine_start', 'fax'])) {
                throw new \RuntimeException('Answered by machine');
            }

            $callId  = $request->query->get('CallId');
            $agentId = $request->query->get('AgentId');

            /** @var VoicePhoneCall $phoneCall */
            if (!$callId || !$phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId)) {
                throw new \RuntimeException('Phone call not found');
            }
            if (!$agentId || !$agent = $this->get('dp.voice.callbacks_helper')->getAgent($agentId)) {
                throw new \RuntimeException('Agent not found');
            }

            if (count($phoneCall->getActiveParticipants()) >= 2) {
                if ($phoneCall->isActive()) {
                    // handle possible race condition
                    // when first agent has accepted the incoming call
                    // but other forwarding calls to others agents hasn't been cancelled yet
                    // it could happen if it's a simultaneous call
                    throw new \RuntimeException('Phone call is already accepted');
                } else {
                    if (!$this->get('dp.voice.task_router')->joinTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
                        throw new \RuntimeException('Unable to join this phone call');
                    }
                }
            } else {
                if (!$this->get('dp.voice.task_router')->acceptTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
                    throw new \RuntimeException('Phone call is already accepted');
                }
            }
        } catch (\Exception $e) {
            $twiml = new Twiml();
            $twiml->hangup();

            $response = new Response($twiml);
            $response->headers->set('Content-Type', 'text/xml');

            return $response;
        }

        /* @var VoicePhoneCall $phoneCall */
        $ticket = $this->get('dp.voice.callbacks_helper')->createOrJoinTicketForIncomingCall($phoneCall, $agent);
        $this->get('event_dispatcher')->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.open-forwarded-ticket', [
                'call_id'   => $phoneCall->getId(),
                'ticket_id' => $ticket->getId(),
                'target'    => $agent->getId(),
            ])
        );

        if (count($phoneCall->getActiveParticipants()) >= 2) {
            return $this->phoneNumberAgentConferenceCallback($account, $request);
        } else {
            return $this->phoneNumberAgentIncomingCallback($request);
        }
    }

    /**
     * Transfer phone call into a conference or hangup.
     * This actions is triggered when user or agent ends the direct call.
     *
     * It may happen in two cases: if the call is ended or it's warm add/transfer.
     *
     * @ApiDoc(
     *     description="Transform direct call into a conference",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/{phoneCall}/on_dial_hangup_callback", name="twilio_on_dial_hangup_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function onDialHangupCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall, Request $request)
    {
        $callSid = $request->get('CallSid');
        $logger  = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[TwilioCallbacks] Begin end dialing queue callback, call_id = %s, uuid = %s',
            $phoneCall->getId(), $callSid
        ));

        $twiml = new Twiml();
        $lock  = $this->get('dp.voice.phone_lock_helper')->createPhoneLock($phoneCall->getId());
        $lock->acquire(true);

        try {
            $this->getManager()->refresh($phoneCall);

            if ($phoneCall->isColdTransfer()) {
                $twiml->redirect($this->getCallRoutingCallbackUrl($account, $phoneCall));
            } elseif ($phoneCall->isWarmAdd()) {
                // if it's warm add user and agent are in an active call
                // so we need to force move into the conference on hang up
                $logger->info(sprintf(
                    '[TwilioCallbacks] Warm transfer, join conference, call_id = %s, uuid = %s',
                    $phoneCall->getId(), $callSid
                ));

                $twiml->dial([
                    'record'                        => 'record-from-answer-dual',
                    'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account, $phoneCall),
                    'recordingStatusCallbackMethod' => 'POST',
                ])->conference($phoneCall->getConferenceName(), [
                    'beep'                 => false,
                    'waitUrl'              => '',
                    'statusCallback'       => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                    'statusCallbackMethod' => 'POST',
                    'statusCallbackEvent'  => 'join leave start end mute hold',
                    'endConferenceOnExit'  => false,
                ]);
            } elseif ($phoneCall->isOnHold()) {
                if ($phoneCall->enqueuedAsAgent()) {
                    $twiml->redirect($this->getHoldMusicCallbackUrl($account, $phoneCall));
                } else {
                    $twiml->redirect($this->getHoldSilentCallbackUrl($account));
                }
            } elseif (!$phoneCall->isVoicemail()) {
                $logger->info(sprintf(
                    '[TwilioCallbacks] End call, call_id = %s, uuid = %s',
                    $phoneCall->getId(), $callSid
                ));

                $twiml->hangup();
            }

            $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);
        } finally {
            $lock->release();
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Put user on hold if it's a direct call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/{phoneCall}/put_on_hold_callback", name="twilio_put_on_hold_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function putOnHoldCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        if ($phoneCall->enqueuedAsAgent()) {
            $waitUrl = $this->getHoldSilentCallbackUrl($account);
        } else {
            $waitUrl = $this->getHoldMusicCallbackUrl($account, $phoneCall);
        }

        $twiml = new Twiml();
        $twiml->enqueue($phoneCall->getQueueName(), [
            'waitUrl'       => $waitUrl,
            'waitUrlMethod' => 'POST',
        ]);

        $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Unhold user if it's a direct call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/{phoneCall}/unhold_direct_call_callback", name="twilio_unhold_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function unholdDirectCallCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        $twiml = new Twiml();
        $dial  = $twiml->dial([
            'action'                        => $this->getOnDialHangupCallbackUrl($account, $phoneCall),
            'method'                        => 'POST',
            'record'                        => 'record-from-answer-dual',
            'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account, $phoneCall),
            'recordingStatusCallbackMethod' => 'POST',
        ]);

        $dial->queue($phoneCall->getQueueName());
        $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Put user on hold",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/{phoneCall}/hold_music_callback", name="twilio_hold_music_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function holdMusicCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        $twiml = new Twiml();
        $task  = $this->container->get('dp.voice.task_router.storage')->getTask($phoneCall->getTaskSid());
        $queue = $this->container->get('dp.voice.voice_task_helper')->getVoiceQueue($task);
        if ($queue && $queue->getLoopAsset()) {
            $twiml->redirect($this->getHoldMusicUrl($account, $queue->getLoopAsset()));
        } else {
            $twiml->play('http://com.twilio.music.classical.s3.amazonaws.com/ClockworkWaltz.mp3', [
                'loop' => 0,
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Put user on hold",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/hold_silent_callback", name="twilio_hold_silent_callback")
     *
     * @param TwilioVoiceAccount $account
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function holdSilentCallbackAction(TwilioVoiceAccount $account)
    {
        $twiml = new Twiml();
        $twiml->pause([
            'length' => 3600,
        ]);
        $twiml->redirect($this->getHoldSilentCallbackUrl($account));

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Creates a task for task router",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/{phoneCall}/new_incoming_call/{targetType}/{targetId}", name="twilio_new_incoming_call_callback")
     * @ParamConverter(name="target", converter="voice_target", options={"targetType": "targetType", "targetId": "targetId"})
     *
     * @param TwilioVoiceAccount  $account
     * @param VoicePhoneCall      $phoneCall
     * @param AbstractVoiceTarget $target
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function newIncomingCallCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall, AbstractVoiceTarget $target)
    {
        $twiml = new Twiml();

        // enqueue task for task router
        $task = $this->get('dp.voice.callbacks_helper')->createTaskForTarget($phoneCall, $target);
        if ($task) {
            $twiml->redirect($this->getCallRoutingCallbackUrl($account, $phoneCall));
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Plays ringing musing",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Post("/{phoneCall}/call_routing", name="twilio_call_routing_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function callRoutingCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        $twiml = new Twiml();
        $twiml->enqueue($phoneCall->getQueueName(), [
            'waitUrl'       => $this->getUserRingMusicCallbackUrl($account, $phoneCall),
            'waitUrlMethod' => 'POST',
        ]);

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Force join conference",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/{phoneCall}/join_conference", name="twilio_join_conference_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function joinConferenceCallbackAction(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        $twiml = new Twiml();
        $twiml->dial()->conference($phoneCall->getConferenceName(), [
            'beep'                 => false,
            'waitUrl'              => '',
            'statusCallback'       => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
            'statusCallbackMethod' => 'POST',
            'statusCallbackEvent'  => 'join leave start end mute hold',
            'endConferenceOnExit'  => false,
        ]);

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Transcribe voicemail",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/transcribe_voicemail", name="twilio_transcribe_voicemail_callback")
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    public function transcribeVoicemailAction(Request $request)
    {
        $recordingSid      = $request->get('RecordingSid');
        $transcriptionText = $request->get('TranscriptionText');

        $em = $this->getManager();

        // try to get voicemail from ticket
        $recording = $em->getRepository(VoiceRecording::class)->findOneBy([
            'recordingSid' => $recordingSid,
        ]);
        if ($recording) {
            $recording->setTranscription($transcriptionText);
            $em->flush();
        }

        // try to get personal agent voicemail
        $recording = $em->getRepository(VoiceMissedAgentCall::class)->findOneBy([
            'recordingSid' => $recordingSid,
        ]);
        if ($recording) {
            $recording->setTranscription($transcriptionText);
            $em->flush();
        }
    }

    /**
     * @ApiDoc(
     *     description="Transcribe action, accepts phone call transcription",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/transcribe_callback", name="twilio_transcribe_callback")
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transcribeAction(TwilioVoiceAccount $account, Request $request)
    {
        $addOns     = json_decode($request->request->get('AddOns'), true);
        $payloadUrL = $addOns['results']['voicebase_transcription']['payload'][0]['url'];

        $client   = new \GuzzleHttp\Client();
        $response = $client->request('GET', $payloadUrL, ['auth' => [$account->getAccountId(), $account->getAuthToken()]]);

        $results = json_decode((string) $response->getBody(), true);
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
        $callId          = $request->query->get('CallId');
        $callSid         = $request->get('CallSid');
        $agentId         = $request->query->get('AgentId');
        $forwardedNumber = $request->get('To');
        $details         = $request->query->all();

        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[TwilioCallbacks] Begin agent incoming callback, uuid = %s, call_id = %s, agent_id = %s, forwarded_number = %s',
            $callSid, $callId, $agentId, $forwardedNumber
        ));

        $twiml = new Twiml();

        try {
            $phoneCall = $this->get('dp.voice.callbacks_helper')->joinIncomingPhoneCall(
                $callId,
                $callSid,
                $agentId,
                $forwardedNumber,
                $details
            );

            if ($phoneCall) {
                /** @var TwilioVoiceAccount $account */
                $account = $phoneCall->getNumber()->getAccount();
                $dial    = $twiml->dial([
                    'action'                        => $this->getOnDialHangupCallbackUrl($account, $phoneCall),
                    'method'                        => 'POST',
                    'record'                        => 'record-from-answer-dual',
                    'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account, $phoneCall),
                    'recordingStatusCallbackMethod' => 'POST',
                ]);

                $dial->queue($phoneCall->getQueueName());

                if ($phoneCall->isColdTransfer()) {
                    // mark the phone call as started
                    $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);
                    $this->getManager()->flush();
                } else {
                    $this->get('dp.voice.callbacks_helper')->logConferenceStart($phoneCall, $details);
                }

                $this->get('event_dispatcher')->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent('agent.voice.call-answered', [
                        'call_id' => $phoneCall->getId(),
                    ])
                );

                $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);
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
     * @throws \Exception
     *
     * @return Response
     */
    private function phoneNumberUserIncomingCallback(Request $request)
    {
        $callSid    = $request->get('CallSid');
        $fromNumber = $request->get('From');
        $toNumber   = $request->get('To');

        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[TwilioCallbacks] Begin user incoming callback, uuid = %s, from = %s, to = %s',
            $callSid, $fromNumber, $toNumber
        ));

        $twiml = new Twiml();

        try {
            $phoneCall = $this->get('dp.voice.callbacks_helper')->createIncomingPhoneCall(
                $callSid,
                $fromNumber,
                $toNumber,
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
        /** @var VoicePhoneCall $phoneCall */
        $callId  = $request->query->get('CallId');
        $callSid = $request->query->get('CallSid');
        $agentId = $request->query->get('AgentId');
        $details = $request->query->all();

        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[TwilioCallbacks] Begin agent outgoing callback, uuid = %s, call_id = %s, agent_id = %s',
            $callSid, $callId, $agentId
        ));

        $twiml     = new Twiml();
        $phoneCall = null;

        try {
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
            if (!$phoneCall) {
                throw $this->createBadRequestException('Phone call not found');
            }

            $this->get('dp.voice.callbacks_helper')->setOutgoingAgentParticipant(
                $callId,
                $callSid,
                $agentId,
                $details
            );

            // make an outbound call
            $callUuid = $this->get('twilio_adapter')->callNumber(
                $phoneCall,
                $phoneCall->getExternalNumber(),
                [
                    'url'                  => $this->getOutboundCallbackUrl($account, $phoneCall),
                    'method'               => 'POST',
                    'statusCallback'       => $this->getPhoneNumberStatusCallbackUrl($account, $phoneCall),
                    'statusCallbackMethod' => 'POST',
                ],
                $exception
            );

            if ($callUuid) {
                $this->get('dp.voice.callbacks_helper')->setOutgoingUserParticipant($callId, $callUuid, $details);

                // create and join a new conference
                $twiml->enqueue($phoneCall->getQueueName(), [
                    'waitUrl'       => $this->getAgentWaitCallbackUrl($account, $phoneCall),
                    'waitUrlMethod' => 'POST',
                ]);

                // outgoing request id is created, not we can cancel outgoing call to prevent race conditions
                $this->get('event_dispatcher')->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent('agent.voice.outgoing-call-init')
                );
            } else {
                $errorCodeGen = $this->get('form_error.error_code_generator.api');

                if ($exception) {
                    if ($exception instanceof BlacklistException) {
                        $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::VOICE_BLACKLIST, [], ['call_to']);
                    } elseif ($exception instanceof InsufficientBalanceException) {
                        $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::INSUFFICIENT_BALANCE, [], ['call_to']);
                    } else {
                        $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::VOICE_PERMISSIONS, [], ['call_to']);
                    }
                } else {
                    $errorMessage = $errorCodeGen->generateByErrorCode(ErrorsCodes::VOICE_UNABLE_TO_CALL_THIS_NUMBER, [], ['call_to']);
                }

                $this->get('event_dispatcher')->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent('agent.voice.outgoing-provider-error', [
                        'call_id' => $phoneCall->getId(),
                        'errors'  => $errorMessage,
                    ])
                );

                $twiml->hangup();

                // mark phone call as ended
                $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
                $this->getManager()->flush();
            }
        } catch (\Exception $e) {
            $twiml->hangup();

            // mark phone call as ended
            if ($phoneCall) {
                $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
                $this->getManager()->flush();
            }
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * Agent answers conference call (invite or transfer).
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    private function phoneNumberAgentConferenceCallback(TwilioVoiceAccount $account, Request $request)
    {
        $callSid         = $request->get('CallSid');
        $callId          = $request->query->get('CallId');
        $agentId         = $request->query->get('AgentId');
        $forwardedNumber = $request->get('To');

        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[TwilioCallbacks] Begin agent conference callback, uuid = %s, call_id = %s, agent_id = %s, forwarded_number = %s',
            $callSid, $callId, $agentId, $forwardedNumber
        ));

        $twiml = new Twiml();

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
        if ($phoneCall) {
            $this->get('dp.voice.callbacks_helper')->joinIncomingPhoneCall(
                $phoneCall->getId(),
                $callSid,
                $agentId,
                $forwardedNumber,
                $request->query->all()
            );

            $twiml->dial()->conference($phoneCall->getConferenceName(), [
                'beep'                 => false,
                'waitUrl'              => '',
                'statusCallback'       => $this->getConferenceStatusCallbackUrl($account, $phoneCall),
                'statusCallbackMethod' => 'POST',
                'statusCallbackEvent'  => 'join leave start end mute hold',
                'endConferenceOnExit'  => false,
            ]);

            // join user or agent to the conference
            // based if it's incoming or outgoing call
            if (!$phoneCall->getConferenceSid()) {
                $providerHelper = $this->get('dp.voice.provider_helper');

                if ($phoneCall->isWarmTransfer()) {
                    // if it's a warm transfer then user and agent are on hold
                    // so we need to move all active participants into the conference
                    foreach ($phoneCall->getActiveAgentParticipants() as $participant) {
                        $providerHelper->transferParticipant(
                            $participant,
                            $this->getJoinConferenceCallbackUrl($account, $phoneCall),
                            'POST'
                        );
                    }
                } elseif ($phoneCall->isWarmAdd()) {
                    // if it's an outgoing call then user was dialing to the queue
                    // and has a reference to the 'on hangup' callback action
                    if ($phoneCall->enqueuedAsAgent()) {
                        foreach ($phoneCall->getAgentParticipants() as $participant) {
                            $providerHelper->transferParticipant(
                                $participant,
                                $this->getJoinConferenceCallbackUrl($account, $phoneCall),
                                'POST'
                            );
                        }
                    } else {
                        // otherwise it's an incoming call
                        // move the end-user directly into the conference
                        // agent will be moved in the 'on hangup' callback action
                        $providerHelper->transferUser(
                            $phoneCall,
                            $this->getJoinConferenceCallbackUrl($account, $phoneCall),
                            'POST'
                        );
                    }
                } elseif ($phoneCall->isOnHold()) {
                    foreach ($phoneCall->getParticipants() as $participant) {
                        $providerHelper->transferParticipant(
                            $participant,
                            $this->getOnDialHangupCallbackUrl($account, $phoneCall),
                            'POST'
                        );

                        $participant->setOnHold(false);
                    }

                    $this->getManager()->flush();
                    $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);
                }
            }
        }

        $this->get('event_dispatcher')->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.call-answered', [
                'call_id' => $phoneCall->getId(),
            ])
        );

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

        if ($target instanceof VoiceAutoAttendantTarget) {
            $autoAttendant = $target->getAutoAttendant();
            $dialNumbers   = $autoAttendant->getOrderedDialNumbers();

            $gather = $twiml->gather([
                'numDigits'           => 1,
                'action'              => $this->getAutoAttendantCallbackUrl($account, $autoAttendant),
                'finishOnKey'         => '',
                'timeout'             => 30,
                'actionOnEmptyResult' => true,
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
                        'For %s, press %d',
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
        } else {
            if ($target instanceof VoiceQueueTarget) {
                if ($target->getQueue()->getGreetAsset()) {
                    $this->playGreetAsset($twiml, $target->getQueue()->getGreetAsset());
                }
            }

            $twiml->redirect($this->getNewIncomingCallCallbackUrl($account, $phoneCall, $target));
        }
    }

    /**
     * @param TwilioVoiceAccount  $account
     * @param VoicePhoneCall      $phoneCall
     * @param AbstractVoiceTarget $target
     *
     * @return string
     */
    private function getNewIncomingCallCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall, AbstractVoiceTarget $target)
    {
        $targetDetails = $target->getTargetDetails();

        return $this->get('router')->generate('twilio_new_incoming_call_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
            'targetId'    => $targetDetails['id'],
            'targetType'  => $targetDetails['type'],
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getUserRingMusicCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_user_ring_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getHoldMusicCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_hold_music_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getHoldSilentCallbackUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_hold_silent_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getAgentWaitCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_agent_wait_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
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
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getRecordingStatusCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_recording_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getVoicemailRecordingStatusCallbackUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_voicemail_recording_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getVoicemailRecordUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_voicemail_record', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
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
            'phoneCall'   => $phoneCall->getId(),
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
     *
     * @return string
     */
    private function getOnDialHangupCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_on_dial_hangup_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getCallRoutingCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_call_routing_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function getJoinConferenceCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_join_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param VoicePhoneCall     $phoneCall
     *
     * @return string
     */
    private function acceptDirectCallCallbackUrl(TwilioVoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_unhold_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string
     */
    private function getTranscribeVoicemailCallbackUrl(TwilioVoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_transcribe_voicemail_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
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
