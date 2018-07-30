<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\JobQueue\Processor\VoiceDownloadRecordProcessor;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceBlobAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\VoiceTextAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoicemailRecord;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantAgent;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use DeskPRO\Bundle\AppBundle\Twilio\Twiml;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
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
class TwilioCallbacksController extends AbstractVoiceController
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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function phoneNumberCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
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
                $callId  = $request->query->get('CallId');
                $agentId = $request->query->get('AgentId');

                if (!$callId || !$phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId)) {
                    throw $this->createBadRequestException('Phone call not found');
                }
                if (!$agentId || !$agent = $this->getAgent($agentId)) {
                    throw $this->createBadRequestException('Agent not found');
                }

                return $this->phoneNumberAgentIncomingCallback($account, $phoneCall, $agent, $request);
            } else {
                // user connection
                return $this->phoneNumberUserIncomingCallback($account, $request);
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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     */
    public function phoneNumberStatusCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $em            = $this->getManager();
        $twilioAdapter = $this->get('twilio_adapter');

        $callSid    = $request->request->get('CallSid');
        $callStatus = $request->request->get('CallStatus');

        if (!$callSid) {
            return;
        }

        if ($callStatus === 'completed') {
            // check voicemail worker status
            // in case if voicemail callback wasn't called for some reason
            $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($phoneCall && $phoneCall->getStatus() === VoicePhoneCall::STATUS_VOICEMAIL) {
                $voicemailWorker = $this->get('twilio_adapter')->getVoicemailWorker($account);
                if ($voicemailWorker->activityName === 'Busy') {
                    $twilioAdapter->updateVoicemailWorkerActivity($account, 'Idle');
                }
            }

            // log call participants
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($participant) {
                /** @var VoicePhoneCall $phoneCall */
                $phoneCall = $participant->getPhoneCall();

                // set participant leave event time
                $participant->setDateLeft(new \DateTime());
                $em->persist($participant);
                $em->flush();

                if ($participant instanceof VoicePhoneCallParticipantUser) {
                    // log end-user ends the call
                    $log = new VoicePhoneCallLog();
                    $log
                        ->setDetails($request->request->all())
                        ->setPerson($participant->getPerson())
                        ->setPhoneCall($participant->getPhoneCall())
                        ->setActionType(VoicePhoneCallLog::ACTION_USER_DISCONNECTED)
                    ;

                    $em->persist($log);
                    $em->flush();

                    // ensure that we completed the end-user task if the phone call was not established
                    // to avoid new reservation creations
                    $twilioAdapter->endTask($account, $phoneCall->getTaskSid());

                    // cancel all ringing forwarding calls
                    $twilioAdapter->cancelForwardingCalls($phoneCall);

                    // mark the phone call as finished
                    $phoneCall->setDateEnded(new \DateTime());
                    $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);

                    // user ends call
                    // check the call is not answered and voicemail wasn't reached
                    // create a ticket for missed calls
                    if (!$phoneCall->hasAgentParticipants() && !$phoneCall->getVoicemailRecord()) {
                        $ticketMessageCall = new TicketMessageVoicePhoneCall();
                        $ticketMessageCall->setPhoneCall($phoneCall);

                        $ticketMessage = new TicketMessage();
                        $ticketMessage->setPerson($phoneCall->getPerson());
                        $ticketMessage->addAttribute($ticketMessageCall);
                        $ticketMessage->setMessage('Missed call from '.$phoneCall->getExternalNumber());
                        $ticketMessage->setAsAgentNote(true);

                        $ticket = new Ticket();
                        $ticket->disableAutoTicketProcess();
                        $ticket->setSubject('Missed call from '.$phoneCall->getExternalNumber());
                        $ticket->setPerson($phoneCall->getPerson());
                        $ticket->addMessage($ticketMessage);

                        $this->saveTicket($ticket);
                    }

                    // log call end event
                    // for now if a end-user finishes the call then it means the conference is ended
                    $log = new VoicePhoneCallLog();
                    $log->setActionType(VoicePhoneCallLog::ACTION_ENDED);
                    $log->setDetails($request->request->all());
                    $log->setPhoneCall($phoneCall);

                    $em->persist($log);
                    $em->persist($phoneCall);
                    $em->flush();
                } else {
                    // log agent ends the call
                    $log = new VoicePhoneCallLog();
                    $log
                        ->setDetails($request->request->all())
                        ->setPerson($participant->getPerson())
                        ->setPhoneCall($participant->getPhoneCall())
                        ->setActionType(VoicePhoneCallLog::ACTION_AGENT_DISCONNECTED)
                    ;

                    $em->persist($log);
                    $em->flush();

                    if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_COLD_TRANSFER) {
                        // original agent was disconnected, change status to pending
                        $phoneCall->setStatus(VoicePhoneCall::STATUS_PENDING);

                        $em->persist($phoneCall);
                        $em->flush();
                    } else {
                        $twilioAdapter->tryEndConference($phoneCall);
                    }
                }
            }
        } elseif ($callStatus === 'busy') {
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $callSid,
            ]);

            if ($participant) {
                $phoneCall = $participant->getPhoneCall();

                // set participant leave event time
                $participant->setDateLeft(new \DateTime());
                $em->persist($participant);
                $em->flush();

                if ($participant instanceof VoicePhoneCallParticipantUser) {
                    // user declined outgoing call
                    // log end-user ends the call
                    $log = new VoicePhoneCallLog();
                    $log
                        ->setDetails($request->request->all())
                        ->setPerson($participant->getPerson())
                        ->setPhoneCall($participant->getPhoneCall())
                        ->setActionType(VoicePhoneCallLog::ACTION_USER_DISCONNECTED);

                    $em->persist($log);
                    $em->flush();

                    // ensure that we completed the end-user task if the phone call was not established
                    // to avoid new reservation creations
                    $twilioAdapter->endTask($account, $phoneCall->getTaskSid());

                    // mark the phone call as finished
                    $phoneCall->setDateEnded(new \DateTime());
                    $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);

                    // log call end event
                    // for now if a end-user finishes the call then it means the conference is ended
                    $log = new VoicePhoneCallLog();
                    $log->setActionType(VoicePhoneCallLog::ACTION_ENDED);
                    $log->setDetails($request->request->all());
                    $log->setPhoneCall($phoneCall);

                    $em->persist($log);
                    $em->persist($phoneCall);
                    $em->flush();

                    $this->get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent.voice.outgoing-call-declined', [
                            'CallSid' => $phoneCall->getCallSid(),
                        ])
                    );
                }
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
                $this->rejectIncomingPhoneCall($phoneCall, $agent);
                $twilioAdapter->rejectAgentWorkerReservations($phoneCall->getNumber()->getAccount(), $agent);
                // set agent worker available for new calls
                $twilioAdapter->updateAgentWorker($account, $agent, 'Idle');
            }
        }
    }

    /**
     * @ApiDoc(
     *     description="Worker assignment callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="array"
     * )
     *
     * @Rest\Post("/assignment_callback", name="twilio_assignment_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function assignmentCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $task = json_decode($request->request->get('TaskAttributes'), true);
        if (!isset($task['deskpro_call_id']) || !isset($task['call_sid'])) {
            throw $this->createBadRequestException('Unable to get task attributes');
        }

        $workerSid = $request->request->get('WorkerSid');
        $queueId   = isset($task['deskpro_queue_id']) ? $task['deskpro_queue_id'] : null;
        $agentId   = isset($task['agent_id']) ? $task['agent_id'] : null;
        $queue     = $queueId ? $this->getRepository(VoiceQueue::class)->find($queueId) : null;
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($task['deskpro_call_id']);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $phoneCall->setTaskSid($request->request->get('TaskSid'));
        $phoneCall->setQueue($queue);

        $em = $this->getManager();
        $em->persist($phoneCall);
        $em->flush();

        if ($account->getVoicemailWorkerSid() === $workerSid) {
            // mark the phone call as completed (redirected to voicemail)
            $phoneCall->setStatus(VoicePhoneCall::STATUS_VOICEMAIL);
            $em->persist($phoneCall);
            $em->flush();

            // return redirect response
            $voicemailAsset = null;
            if ($queue) {
                // get custom queue voicemail asset
                $voicemailAsset = $queue->getVoicemailAsset();

                // create voicemail queue ticket
                $ticketMessageCall = new TicketMessageVoicePhoneCall();
                $ticketMessageCall->setPhoneCall($phoneCall);

                $ticketMessage = new TicketMessage();
                $ticketMessage->setPerson($phoneCall->getPerson());
                $ticketMessage->addAttribute($ticketMessageCall);
                $ticketMessage->setMessage('Call from '.$phoneCall->getExternalNumber());
                $ticketMessage->setAsAgentNote(true);

                $ticket = new Ticket();
                $ticket->disableAutoTicketProcess();
                $ticket->setSubject('Voicemail from '.$phoneCall->getExternalNumber());
                $ticket->setPerson($phoneCall->getPerson());
                $ticket->addMessage($ticketMessage);

                // set asset properties
                if ($queue->getVoicemailAgent()) {
                    $ticket->setAgent($queue->getVoicemailAgent());
                }
                if ($queue->getVoicemailAgentTeam()) {
                    $ticket->setAgentTeam($queue->getVoicemailAgentTeam());
                }
                if ($queue->getVoicemailDepartment()) {
                    $ticket->setDepartment($queue->getVoicemailDepartment());
                }

                $this->saveTicket($ticket);
            } elseif ($agentId) {
                $agent     = $this->getAgent($agentId);
                $agentData = $agent->getAgentData();
                if (!$agentData) {
                    throw $this->createBadRequestException();
                }

                $voicemailAsset = $agentData->getVoicemailAsset();

                $voicemailRecord = new VoicemailRecord();
                $voicemailRecord
                    ->setPhoneCall($phoneCall)
                    ->setAgent($agent)
                    ->setData($request->request->all())
                ;

                $em->persist($voicemailRecord);
                $em->flush();
            }

            $response = [
                'instruction' => 'redirect',
                'call_sid'    => $task['call_sid'],
                'url'         => $this->getVoicemailUrl($account, $voicemailAsset),
                'accept'      => true,
            ];
        } else {
            // got agent worker, redirect to the agent UI
            $qb = $this->getManager()->createQueryBuilder();
            $qb
                ->select('p')
                ->from(Person::class, 'p')
                ->join('p.agentData', 'a')
                ->where('a.voiceWorkerSid = :worker_sid')
                ->setParameter('worker_sid', $workerSid)
            ;

            /** @var Person $agent */
            $agent = $qb->getQuery()->getOneOrNullResult();
            if (!$agent) {
                throw $this->createBadRequestException('Worker agent not found');
            }

            $loopAsset = null;
            if ($queue) {
                $loopAsset = $queue->getLoopAsset();
            }

            $response = [
                'instruction' => 'redirect',
                'call_sid'    => $task['call_sid'],
                'url'         => $this->getConferenceCallbackUrl($account, $agent, $loopAsset),
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @ApiDoc(
     *     description="Create conference callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     output="string"
     * )
     *
     * @Rest\Post("/conference_callback/{agent}", name="twilio_conference_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Person       $agent
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function conferenceCallbackAction(VoiceAccount $account, $accountAuth, Person $agent, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid   = $request->request->get('CallSid');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['callSid' => $callSid]);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $asset   = null;
        $assetId = $request->query->get('asset');
        if ($assetId) {
            $asset = $this->getRepository(AbstractVoiceAsset::class)->find($assetId);
        }

        $agentData = $agent->getAgentData();
        if ($agentData->agentCanUseForwarding() && $agentData->canUseForwarding() && $agentData->getForwardingNumber()) {
            // make an outbound call
            $call = $this->get('twilio_adapter')->callNumber($phoneCall->getNumber(), $agentData->getForwardingNumber(), [
                'url'                  => $this->getAnswerForwardingUrl($account, $phoneCall, $agent),
                'method'               => 'POST',
                'statusCallback'       => $this->getPhoneNumberStatusCallbackUrl($account, $phoneCall, $agent),
                'statusCallbackMethod' => 'POST',
            ]);

            $phoneCall->addForwardingSid($call->sid);

            $em = $this->getManager();
            $em->persist($phoneCall);
            $em->flush();
        }

        $options = [
            'endConferenceOnExit'           => true,
            'statusCallback'                => $this->getConferenceStatusCallbackUrl($account),
            'statusCallbackMethod'          => 'POST',
            'statusCallbackEvent'           => 'join leave start end mute hold',
            'record'                        => 'record-from-start',
            'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account),
            'recordingStatusCallbackMethod' => 'POST',
        ];

        if ($asset) {
            $options['waitUrl']    = $this->getHoldMusicUrl($account, $asset);
            $options['waitMethod'] = 'POST';
        }

        $twiml = new Twiml();
        $twiml->dial()->conference($this->getConferenceName($phoneCall), $options);

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
     * @Rest\Post("/conference_status_callback", name="twilio_conference_status_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     */
    public function conferenceStatusCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $parameters    = $request->request;
        $callSid       = $parameters->get('CallSid');
        $conferenceSid = $parameters->get('ConferenceSid');
        $eventName     = $parameters->get('StatusCallbackEvent');

        $adapter = $this->get('twilio_adapter');
        $em      = $this->getManager();

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'conferenceSid' => $conferenceSid,
        ]);

        // we set conference sid on first user participant join
        // otherwise we should have the phone call tied to voice model
        if (!$phoneCall && $eventName !== 'participant-join') {
            throw $this->createBadRequestException('Phone call not found');
        }

        // handle conference events
        if ($eventName === 'conference-start') {
            // mark the phone call as started
            $phoneCall->setDateStarted(new \DateTime());
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);

            // log conference start event
            $log = new VoicePhoneCallLog();
            $log->setActionType(VoicePhoneCallLog::ACTION_STARTED);
            $log->setDetails($request->request->all());
            $log->setPhoneCall($phoneCall);

            $em->persist($log);
            $em->persist($phoneCall);
            $em->flush();
        } elseif ($eventName === 'participant-join') {
            // if didn't get the phone call by conference sid then the initial caller didn't join the conference yet
            // store conference sid on its join callback
            if (!$phoneCall) {
                $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['callSid' => $callSid]);
                if (!$phoneCall) {
                    throw $this->createBadRequestException('Phone call not found');
                }

                $phoneCall->setConferenceSid($conferenceSid);

                $em->persist($phoneCall);
                $em->flush();
            }

            $participant = $phoneCall->getParticipantByCallSid($callSid);
            if ($participant instanceof VoicePhoneCallParticipantAgent) {
                // set participant join event time
                $participant->setDateJoined(new \DateTime());
                $em->persist($participant);
                $em->flush();

                // unhold the conference, could be on cold transfer
                if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_PENDING) {
                    $adapter->holdConferenceEndUser($phoneCall, false);
                }

                // log participant join event
                $log = new VoicePhoneCallLog();
                $log->setDetails($request->request->all());
                $log->setPhoneCall($phoneCall);
                $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_JOINED);
                if ($participant->getPerson()) {
                    $log->setPerson($participant->getPerson());
                }

                $em->persist($log);
                $em->flush();
            }
        } elseif (in_array($eventName, ['participant-hold', 'participant-unhold', 'participant-mute', 'participant-unmute'])) {
            $actionTypeMapping = [
                'participant-hold'   => VoicePhoneCallLog::ACTION_HOLD,
                'participant-unhold' => VoicePhoneCallLog::ACTION_UNHOLD,
                'participant-mute'   => VoicePhoneCallLog::ACTION_MUTED,
                'participant-unmute' => VoicePhoneCallLog::ACTION_UNMUTED,
            ];

            $log = new VoicePhoneCallLog();
            $log->setActionType($actionTypeMapping[$eventName]);
            $log->setPerson($phoneCall->getPersonByCallSid($callSid));
            $log->setDetails($request->request->all());
            $log->setPhoneCall($phoneCall);

            $em->persist($log);
            $em->flush();
        }

        // send client message
        // for real time ui updates
        $statusParams = $request->request->all();
        if ($phoneCall) {
            // phone call
            $statusParams['phone_call'] = $this->get('serializer')->toArray($phoneCall, new SideloadSerializationContext());
            unset($statusParams['phone_call']['ticket']);

            // current participant
            $participant = $phoneCall->getPersonByCallSid($callSid);
            if ($participant) {
                $statusParams['agent_id'] = $participant->getId();
            }

            // all active participants
            $statusParams['agent_participants'] = array_map(function (Person $person) {
                return $person->getId();
            }, $adapter->getActivePhoneCallParticipants($phoneCall));

            // is conference on hold
            $statusParams['hold'] = $adapter->isConferenceOnHold($phoneCall);
        }

        $this->get('event_dispatcher')->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.conference.status', $statusParams)
        );
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
     * @param VoiceAccount       $account
     * @param string             $accountAuth
     * @param VoiceAutoAttendant $autoAttendant
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function autoAttendantCallbackAction(VoiceAccount $account, $accountAuth, VoiceAutoAttendant $autoAttendant, Request $request)
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

        $enteredCode = $request->request->get('Digits');
        if (is_numeric($enteredCode)) {
            $dialNumber = $autoAttendant->getDialNumber((int) $enteredCode);
            if ($dialNumber) {
                $this->addTargetResponse($phoneCall, $dialNumber->getTarget(), $twiml);

                // log auto-attendant press key event
                $log = new VoicePhoneCallLog();
                $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_KEY);
                $log->setDetails(array_merge($request->request->all(), [
                    'target_name' => $dialNumber->getTarget()->getTargetName(),
                ]));
                $log->setPhoneCall($phoneCall);

                $em = $this->getManager();
                $em->persist($log);
                $em->flush();
            } else {
                $number = $phoneCall->getNumber();
                $target = $number->getTarget();

                $twiml->say('Required dial number is not supported.', [
                    'voice' => 'alice',
                ]);

                $this->addTargetResponse($phoneCall, $target, $twiml);

                // log auto-attendant press key event
                $log = new VoicePhoneCallLog();
                $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_UNSUPPORTED_KEY);
                $log->setDetails($request->request->all());
                $log->setPhoneCall($phoneCall);

                $em = $this->getManager();
                $em->persist($log);
                $em->flush();
            }
        } elseif ($enteredCode === '*') {
            $number = $phoneCall->getNumber();
            $target = $number->getTarget();

            $this->addTargetResponse($phoneCall, $target, $twiml);

            // log auto-attendant press key event
            $log = new VoicePhoneCallLog();
            $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_REPEAT_KEY);
            $log->setDetails($request->request->all());
            $log->setPhoneCall($phoneCall);

            $em = $this->getManager();
            $em->persist($log);
            $em->flush();
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

            // log auto-attendant press key event
            $log = new VoicePhoneCallLog();
            $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_EXTENSION_KEY);
            $log->setDetails($request->request->all());
            $log->setPhoneCall($phoneCall);

            $em = $this->getManager();
            $em->persist($log);
            $em->flush();
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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function agentExtensionCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
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

        $enteredCode = $request->request->get('Digits');

        $twiml = new Twiml();

        $agentData = $this->getRepository(AgentData::class)->findOneBy([
            'extensionNumber' => $enteredCode,
        ]);
        if ($agentData) {
            $target = new VoiceAgentTarget();
            $target->setAgent($agentData->getPerson());

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
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_EXTENSION);
        $log->setDetails($request->request->all());
        $log->setPhoneCall($phoneCall);

        $em = $this->getManager();
        $em->persist($log);
        $em->flush();

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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     */
    public function recordingStatusCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $em     = $this->getManager();
        $source = $request->request->get('RecordingSource');

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
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $phoneCall->setDuration($request->request->get('RecordingDuration'));
        $phoneCall->setData(array_merge($phoneCall->getData(), [
            'RecordingUrl' => $request->request->get('RecordingUrl'),
        ]));

        $em->persist($phoneCall);
        $em->flush();

        $recordingEnabled = true;
        if ($phoneCall->getQueue()) {
            $recordingEnabled = $phoneCall->getQueue()->isRecordingEnabled();
        }

        if ($recordingEnabled) {
            $this->getContainer()->getJobQueue()->addJob(new Job(VoiceDownloadRecordProcessor::JOB_TYPE, [
                'call_id' => $phoneCall->getId(),
            ]));
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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        // set the worker available as soon as callback called
        // to wait for other voicemail messages
        $this->get('twilio_adapter')->updateVoicemailWorkerActivity($account, 'Idle');

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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function voicemailEndAction(VoiceAccount $account, $accountAuth)
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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function outgoingCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callId = $request->query->get('callId');
        if (!$callId) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $agentParticipant = $phoneCall->getAgentParticipants()->first();
        $agent            = $agentParticipant instanceof VoicePhoneCallParticipantAgent ? $agentParticipant->getPerson() : null;

        $ticketMessageCall = new TicketMessageVoicePhoneCall();
        $ticketMessageCall->setPhoneCall($phoneCall);

        $ticketMessage = new TicketMessage();
        $ticketMessage->setPerson($phoneCall->getPerson());
        $ticketMessage->addAttribute($ticketMessageCall);
        $ticketMessage->setMessage('Call to '.$phoneCall->getExternalNumber());
        $ticketMessage->setAsAgentNote(true);

        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->setSubject('Call to '.$phoneCall->getExternalNumber());
        $ticket->setPerson($phoneCall->getPerson());
        $ticket->setAgent($agent);
        $ticket->addMessage($ticketMessage);

        $this->saveTicket($ticket);

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.outgoing-call-answered',
            [
                'ticket'  => $this->get('serializer')->toArray($ticket, new SideloadSerializationContext()),
                'CallSid' => $agentParticipant->getCallSid(),
            ]
        ));

        $twiml = new Twiml();
        $twiml->dial()->conference($this->getConferenceName($phoneCall), [
            'endConferenceOnExit'           => true,
            'statusCallback'                => $this->getConferenceStatusCallbackUrl($account),
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
     * @Rest\Post("/hold_music", name="twilio_hold_music")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function holdMusicAction(VoiceAccount $account, $accountAuth, Request $request)
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
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function answeredForwardingCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callId  = $request->query->get('CallId');
        $agentId = $request->query->get('AgentId');

        if (!$callId || !$phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId)) {
            throw $this->createBadRequestException('Phone call not found');
        }
        if (!$agentId || !$agent = $this->getAgent($agentId)) {
            throw $this->createBadRequestException('Agent not found');
        }

        if (!$this->get('twilio_adapter')->acceptTaskInForwardingCall($phoneCall, $agent)) {
            $twiml = new Twiml();
            $twiml->hangup();

            $response = new Response($twiml);
            $response->headers->set('Content-Type', 'text/xml');

            return $response;
        }

        $this->createOrJoinTicketForIncomingCall($phoneCall, $agent);

        return $this->phoneNumberAgentIncomingCallback($account, $phoneCall, $agent, $request);
    }

    /**
     * @return string
     */
    private function getHelpdeskName()
    {
        return $this->getContainer()->getBrandSetting('core.deskpro_name');
    }

    /**
     * @param VoiceAccount   $account
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param Request        $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    private function phoneNumberAgentIncomingCallback(VoiceAccount $account, VoicePhoneCall $phoneCall, Person $agent, Request $request)
    {
        if (!$phoneCall->getConferenceSid()) {
            throw $this->createBadRequestException('No conference was created for this phone call');
        }

        $conference = $this->get('twilio_adapter')->getConference($account, $phoneCall->getConferenceSid());
        if (!$conference) {
            throw $this->createBadRequestException('Conference not found');
        }

        if ($conference->status === 'completed') {
            $twiml = new Twiml();
            $twiml->hangup();
        } else {
            // create the agent participant
            $participant = new VoicePhoneCallParticipantAgent();
            $participant->setCallSid($request->get('CallSid'));
            $participant->setPerson($agent);

            $phoneCall->addParticipant($participant);

            $em = $this->getManager();
            $em->persist($phoneCall);
            $em->flush();

            // log answering event
            $log = new VoicePhoneCallLog();
            $log->setPerson($agent);
            $log->setPhoneCall($phoneCall);

            $details = $request->query->all();
            if ($request->get('To')) {
                $log->setActionType(VoicePhoneCallLog::ACTION_FORWARD_ANSWERED);
                $log->setDetails(array_merge($details, [
                    'forwarded_number' => $request->get('To'),
                ]));
            } else {
                $log->setActionType(VoicePhoneCallLog::ACTION_ANSWERED);
                $log->setDetails($details);
            }

            $em->persist($log);
            $em->flush();

            $twiml = new Twiml();
            $twiml->dial()->conference($this->getConferenceName($phoneCall), [
                'beep'    => false,
                'waitUrl' => '',
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return Response
     */
    private function phoneNumberUserIncomingCallback(VoiceAccount $account, Request $request)
    {
        $query  = $request->query;
        $number = $this->getRepository(VoiceNumber::class)->findOneBy([
            'number'  => $query->get('To'),
            'account' => $account,
        ]);

        if (!$number || !$number->getTarget()) {
            $twiml = new Twiml();
            $twiml->say(sprintf('Thank you for calling, %s', $this->getHelpdeskName()), [
                'voice' => 'alice',
            ]);
            $twiml->say('Required phone number is out of service.', [
                'voice' => 'alice',
            ]);
        } else {
            $em = $this->getManager();

            $phoneNumber = $query->get('From');
            $callSid     = $query->get('CallSid');

            // get the caller person
            /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
            $personRepo = $this->getRepository(Person::class);
            $person     = $personRepo->getOrCreateUserByPhoneNumber($phoneNumber);

            // create phone call
            $phoneCall = new VoicePhoneCall();
            $phoneCall
                ->setCallSid($callSid)
                ->setNumber($number)
                ->setExternalNumber($phoneNumber)
                ->setPerson($person)
                ->setType(VoicePhoneCall::DIRECTION_INBOUND)
                ->setData($query->all())
            ;

            // create user participant
            $participant = new VoicePhoneCallParticipantUser();
            $participant->setCallSid($callSid);
            $participant->setPerson($person);

            $phoneCall->addParticipant($participant);

            // add incoming log
            $log = new VoicePhoneCallLog();
            $log->setActionType(VoicePhoneCallLog::ACTION_NEW_INCOMING);
            $log->setPerson($person);
            $log->setDetails($request->query->all());
            $log->setPhoneCall($phoneCall);

            $em->persist($log);
            $em->persist($phoneCall);
            $em->flush();

            // create twilio new task response
            $twiml = new Twiml();
            if ($account->getQueueWorkflowSid()) {
                $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $twiml);

                // log auto-attendant press key event
                $log = new VoicePhoneCallLog();
                $log->setActionType(VoicePhoneCallLog::ACTION_CALL_TARGET);
                $log->setDetails(array_merge($request->request->all(), [
                    'target_name' => $phoneCall->getNumber()->getTarget()->getTargetName(),
                ]));
                $log->setPhoneCall($phoneCall);

                $em = $this->getManager();
                $em->persist($log);
                $em->flush();
            }
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    private function phoneNumberAgentOutgoingCallback(VoiceAccount $account, Request $request)
    {
        $query     = $request->query;
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($query->get('CallId'));

        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        // get the caller person
        $agent   = $this->getAgent($request->query->get('AgentId'));
        $callSid = $query->get('CallSid');

        $phoneCall
            ->setCallSid($callSid)
            ->setData($query->all())
        ;

        // create agent participant
        $participant = new VoicePhoneCallParticipantAgent();
        $participant->setCallSid($callSid);
        $participant->setPerson($agent);

        $phoneCall->addParticipant($participant);

        // add outgoing log
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_NEW_OUTGOING);
        $log->setPerson($phoneCall->getPerson());
        $log->setDetails($request->query->all());
        $log->setPhoneCall($phoneCall);

        $em = $this->getManager();
        $em->persist($log);
        $em->persist($phoneCall);
        $em->flush();

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

            // create user participant
            $participant = new VoicePhoneCallParticipantUser();
            $participant->setCallSid($call->sid);
            $participant->setPerson($phoneCall->getPerson());

            $phoneCall->addParticipant($participant);

            $em->persist($phoneCall);
            $em->flush();

            // create and join a new conference
            $dial = $twiml->dial(['callerId' => $query->get('From')]);
            $dial->conference($this->getConferenceName($phoneCall), [
                'waitUrl'                       => '',
                'statusCallback'                => $this->getConferenceStatusCallbackUrl($account),
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
                     Please check your international permissions to ensure you can call to this country.',
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
     * @param VoicePhoneCall $phoneCall
     *
     * @return string
     */
    private function getConferenceName(VoicePhoneCall $phoneCall)
    {
        return 'conference'.$phoneCall->getId();
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
        $person  = $phoneCall->getPerson();

        if ($target instanceof VoiceQueueTarget) {
            $queue = $target->getQueue();
            $this->playGreetAsset($twiml, $queue->getGreetAsset());

            $twiml
                ->enqueue([
                    'workflowSid' => $account->getQueueWorkflowSid(),
                ])->task(json_encode([
                    'deskpro_call_id'   => $phoneCall->getId(),
                    'deskpro_queue_id'  => $queue->getId(),
                    'deskpro_person_id' => $person ? $person->getId() : null,
                    'rejected_workers'  => [],
                ]))
            ;
        } elseif ($target instanceof VoiceAgentTarget) {
            $twiml
                ->enqueue([
                    'workflowSid' => $account->getQueueWorkflowSid(),
                ])->task(json_encode([
                    'deskpro_call_id'   => $phoneCall->getId(),
                    'agent_id'          => $target->getAgent()->getId(),
                    'deskpro_person_id' => $person ? $person->getId() : null,
                    'rejected_workers'  => [],
                ]))
            ;
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
                $gather->say('Welcome to '.$this->getHelpdeskName(), [
                    'voice' => 'alice',
                ]);
                $gather->pause([
                    'length' => 2,
                ]);

                foreach ($dialNumbers as $dialNumber) {
                    $gather->say(sprintf(
                        'For call %s, press %d',
                        $dialNumber->getTarget()->getTargetName(), $dialNumber->getDialNum()
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
    }

    /**
     * @param int $agentId
     *
     * @throws \Exception
     *
     * @return Person
     */
    private function getAgent($agentId)
    {
        $agent = $this->getRepository(Person::class)->find($agentId);
        if (!$agent || !$agent->isAgent()) {
            throw $this->createBadRequestException('Agent not found');
        }
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Agent voice is not enabled');
        }

        return $agent;
    }

    /**
     * @param VoiceAccount       $account
     * @param Person             $person
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getConferenceCallbackUrl(VoiceAccount $account, Person $person, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('twilio_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'agent'       => $person->getId(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getConferenceStatusCallbackUrl(VoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_conference_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount       $account
     * @param VoiceAutoAttendant $autoAttendant
     *
     * @return string
     */
    private function getAutoAttendantCallbackUrl(VoiceAccount $account, VoiceAutoAttendant $autoAttendant)
    {
        return $this->get('router')->generate('twilio_auto_attendant_callback', [
            'account'       => $account->getId(),
            'accountAuth'   => $account->getAccountAuth(),
            'autoAttendant' => $autoAttendant->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getAgentExtensionCallbackUrl(VoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_agent_extension_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getRecordingStatusCallbackUrl(VoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_recording_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount       $account
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getVoicemailUrl(VoiceAccount $account, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('twilio_voicemail', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getVoicemailEndUrl(VoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_voicemail_end', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount       $account
     * @param AbstractVoiceAsset $asset
     *
     * @return string
     */
    private function getHoldMusicUrl(VoiceAccount $account, AbstractVoiceAsset $asset = null)
    {
        return $this->get('router')->generate('twilio_hold_music', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount   $account
     * @param VoicePhoneCall $phoneCall
     *
     * @return string
     */
    private function getOutboundCallbackUrl(VoiceAccount $account, VoicePhoneCall $phoneCall)
    {
        return $this->get('router')->generate('twilio_outbound_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount   $account
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return string
     */
    private function getPhoneNumberStatusCallbackUrl(VoiceAccount $account, VoicePhoneCall $phoneCall, Person $agent = null)
    {
        return $this->get('router')->generate('twilio_phone_number_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
            'agentId'     => $agent ? $agent->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param VoiceAccount   $account
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return string
     */
    private function getAnswerForwardingUrl(VoiceAccount $account, VoicePhoneCall $phoneCall, Person $agent)
    {
        return $this->get('router')->generate('twilio_answer_forwarding_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'CallId'      => $phoneCall->getId(),
            'AgentId'     => $agent->getId(),
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
