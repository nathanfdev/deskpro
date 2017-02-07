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

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\JobQueue\Processor\VoiceDownloadRecordProcessor;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantAgent;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Twiml;

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
    use TicketSaveTrait;

    /**
     * @ApiDoc(
     *     description="Handle incoming phone call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Get("/phone_number_callback", name="twilio_phone_number_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function phoneNumberCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        if (strpos($request->query->get('From'), 'client:') === 0) {
            // agent connection
            return $this->phoneNumberAgentCallback($account, $request);
        } else {
            // user connection
            return $this->phoneNumberUserCallback($account, $request);
        }
    }

    /**
     * @ApiDoc(
     *     description="Handle incoming phone call changed status",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/phone_number_status_callback", name="twilio_phone_number_status_callback")
     *
     * @param VoiceAccount $account
     * @param $accountAuth
     * @param Request $request
     */
    public function phoneNumberStatusCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        if ($request->request->get('CallSid')) {
            $participant = $this->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
                'callSid' => $request->request->get('CallSid'),
            ]);

            if ($participant) {
                $log = new VoicePhoneCallLog();
                $log
                    ->setDetails($request->request->all())
                    ->setPerson($participant->getPerson())
                    ->setPhoneCall($participant->getPhoneCall())
                ;

                if ($participant instanceof VoicePhoneCallParticipantUser) {
                    $log->setActionType(VoicePhoneCallLog::ACTION_USER_DISCONNECTED);
                } else {
                    $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_DISCONNECTED);
                }

                $em = $this->getManager();
                $em->persist($log);
                $em->flush();
            }
        }
    }

    /**
     * @ApiDoc(
     *     description="Worker assignment callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/assignment_callback", name="twilio_assignment_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function assignmentCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('p')
            ->from(Person::class, 'p')
            ->join('p.agentData', 'a')
            ->where('a.voiceWorkerSid = :worker_sid')
            ->setParameter('worker_sid', $request->request->get('WorkerSid'))
        ;

        /** @var Person $agent */
        $agent = $qb->getQuery()->getOneOrNullResult();
        if (!$agent) {
            throw $this->createBadRequestException('Worker agent not found');
        }

        $task = json_decode($request->request->get('TaskAttributes'), true);
        if (!isset($task['deskpro_call_id']) || !isset($task['call_sid'])) {
            throw $this->createBadRequestException('Unable to get task attributes');
        }

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($task['deskpro_call_id']);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $phoneCall->setTaskSid($request->request->get('TaskSid'));

        $this->getManager()->persist($phoneCall);
        $this->getManager()->flush();

        $response = [
            'instruction' => 'redirect',
            'call_sid'    => $task['call_sid'],
            'url'         => $this->getConferenceCallbackUrl($account),
        ];

        return new JsonResponse($response);
    }

    /**
     * @ApiDoc(
     *     description="Create conference callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/conference_callback", name="twilio_conference_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function conferenceCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid   = $request->request->get('CallSid');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['callSid' => $callSid]);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $twiml = new Twiml();
        $this->addConferenceResponse($phoneCall, $twiml);

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Conference status callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/conference_status_callback", name="twilio_conference_status_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
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

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['conferenceSid' => $conferenceSid]);

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
            if ($participant) {
                // set participant join event time
                $participant->setDateJoined(new \DateTime());
                $em->persist($participant);
                $em->flush();

                // unhold the conference, could be on cold transfer
                if ($participant instanceof VoicePhoneCallParticipantAgent && $phoneCall->getStatus() === VoicePhoneCall::STATUS_PENDING) {
                    $adapter->holdConferenceEndUser($phoneCall, false);
                }

                // log participant join event
                $log = new VoicePhoneCallLog();
                $log->setDetails($request->request->all());
                $log->setPhoneCall($phoneCall);
                if ($participant) {
                    if ($participant->getPerson()) {
                        $log->setPerson($participant->getPerson());
                    }

                    if ($participant instanceof VoicePhoneCallParticipantAgent) {
                        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_JOINED);
                    } else {
                        $log->setActionType(VoicePhoneCallLog::ACTION_USER_JOINED);
                    }
                }

                $em->persist($log);
                $em->flush();
            }
        } elseif ($eventName === 'participant-leave') {
            // set participant leave event time
            $participant = $phoneCall->getParticipantByCallSid($callSid);
            if ($participant) {
                $participant->setDateLeft(new \DateTime());
                $em->persist($participant);
                $em->flush();
            }

            // log participant leave event
            $log = new VoicePhoneCallLog();
            $log->setDetails($request->request->all());
            $log->setPhoneCall($phoneCall);
            if ($participant) {
                if ($participant->getPerson()) {
                    $log->setPerson($participant->getPerson());
                }

                if ($participant instanceof VoicePhoneCallParticipantAgent) {
                    $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_LEFT);
                } else {
                    $log->setActionType(VoicePhoneCallLog::ACTION_USER_LEFT);
                }
            }

            $em->persist($log);
            $em->flush();

            if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_COLD_TRANSFER) {
                // original agent was disconnected, change status to pending
                $phoneCall->setStatus(VoicePhoneCall::STATUS_PENDING);

                $em->persist($phoneCall);
                $em->flush();
            } else {
                $adapter->tryEndConference($phoneCall);
            }
        } elseif ($eventName === 'conference-end') {
            // ensure that we completed the end-user task if the phone call was not established
            // to avoid new reservation creations
            $adapter->endTask($account, $phoneCall->getTaskSid());

            // mark the phone call as finished
            $phoneCall->setDateEnded(new \DateTime());
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);

            // log conference end event
            $log = new VoicePhoneCallLog();
            $log->setActionType(VoicePhoneCallLog::ACTION_ENDED);
            $log->setDetails($request->request->all());
            $log->setPhoneCall($phoneCall);

            $em->persist($log);
            $em->persist($phoneCall);
            $em->flush();
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
            $serializer        = $this->get('serializer');
            $serializerContext = new SideloadSerializationContext();

            // phone call
            $statusParams['phone_call'] = $serializer->toArray($phoneCall, $serializerContext);

            // current participant
            $participant = $phoneCall->getPersonByCallSid($callSid);
            if ($participant) {
                $statusParams['agent_id'] = $participant->getId();
            }

            // all active participants
            if (in_array($eventName, ['participant-join', 'participant-leave'])) {
                $statusParams['agent_participants'] = array_map(function (Person $person) {
                    return $person->getId();
                }, $adapter->getActivePhoneCallParticipants($phoneCall));
            }

            // is conference on hold
            $statusParams['hold'] = $adapter->isConferenceOnHold($phoneCall);
        }

        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.conference.status');
        $cm->setData($statusParams);

        $em->persist($cm);
        $em->flush();
    }

    /**
     * @ApiDoc(
     *     description="Auto attendant callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/auto_attendant_callback/{autoAttendant}", name="twilio_auto_attendant_callback")
     *
     * @param VoiceAccount       $account
     * @param string             $accountAuth
     * @param VoiceAutoAttendant $autoAttendant
     * @param Request            $request
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
            } else {
                $number = $phoneCall->getNumber();
                $target = $number->getTarget();

                $twiml->say('Required dial number is not supported.');
                $this->addTargetResponse($phoneCall, $target, $twiml);
            }
        } elseif ($enteredCode === '*') {
            $number = $phoneCall->getNumber();
            $target = $number->getTarget();

            $this->addTargetResponse($phoneCall, $target, $twiml);
        } elseif ($enteredCode === '#') {
            $twiml
                ->gather([
                    'numDigits' => 4,
                    'action'    => $this->getAgentExtensionCallbackUrl($account),
                ])
                ->say('Please enter agent extension number')
            ;
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
     *     }
     * )
     *
     * @Rest\Post("/agent_extension_callback", name="twilio_agent_extension_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
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
                ->say(sprintf(
                    'Requested agent with %d extension number does not exist. Please enter agent extension number again.',
                    $enteredCode
                ))
            ;
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
     *     }
     * )
     *
     * @Rest\Post("/recording_status_callback", name="twilio_recording_status_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function recordingStatusCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy([
            'conferenceSid' => $request->request->get('ConferenceSid'),
        ]);
        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $phoneCall->setData(array_merge($phoneCall->getData(), [
            'RecordingUrl' => $request->request->get('RecordingUrl'),
        ]));

        $em = $this->getManager();
        $em->persist($phoneCall);
        $em->flush();

        $this->getContainer()->getJobQueue()->addJob(new Job(VoiceDownloadRecordProcessor::JOB_TYPE, [
            'call_id' => $phoneCall->getId(),
        ]));
    }

    /**
     * @return string
     */
    private function getHelpdeskName()
    {
        return $this->getContainer()->getBrandSetting('core.deskpro_name');
    }

    /**
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return Response
     */
    private function phoneNumberAgentCallback(VoiceAccount $account, Request $request)
    {
        $callId    = $request->query->get('CallId');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);

        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }
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
            $agent = $this->getRepository(Person::class)->find($request->query->get('AgentId'));
            if (!$agent || !$agent->isAgent()) {
                throw $this->createBadRequestException('Agent not found');
            }
            if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
                throw $this->createBadRequestException('Agent voice is not enabled');
            }

            $em = $this->getManager();

            // create the agent participant
            $participant = new VoicePhoneCallParticipantAgent();
            $participant->setCallSid($request->query->get('CallSid'));
            $participant->setPerson($agent);

            $phoneCall->addParticipant($participant);

            $em->persist($phoneCall);
            $em->flush();

            // log answering event
            $log = new VoicePhoneCallLog();
            $log->setActionType(VoicePhoneCallLog::ACTION_ANSWERED);
            $log->setPerson($agent);
            $log->setDetails($request->query->all());
            $log->setPhoneCall($phoneCall);

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
    private function phoneNumberUserCallback(VoiceAccount $account, Request $request)
    {
        $query  = $request->query;
        $number = $this->getRepository(VoiceNumber::class)->findOneBy([
            'number'  => $query->get('To'),
            'account' => $account,
        ]);

        if (!$number || !$number->getTarget()) {
            $twiml = new Twiml();
            $twiml->say(sprintf('Thank you for calling, %s', $this->getHelpdeskName()));
            $twiml->say('Required phone number is out of service.');
        } else {
            $em = $this->getManager();

            $phoneNumber = $query->get('From');
            $callSid     = $query->get('CallSid');

            // get the caller person
            $person = null;
            if ($phoneNumber) {
                // check for an existing person
                $personNumberEntity = $this->getRepository(PhoneNumber::class)->findOneBy(['number' => $phoneNumber]);
                if ($personNumberEntity) {
                    $person = $personNumberEntity->getPerson();
                }

                // if person was not found then create a new one
                if (!$person) {
                    $person = new Person();
                    $person->setPrimaryPhoneNumber(PhoneNumber::createEntity($phoneNumber));
                    $person->setEmail('incoming.call.'.$phoneNumber.'@example.com');

                    $em->persist($person);
                    $em->flush();
                }
            }

            $phoneCall = new VoicePhoneCall();
            $phoneCall
                ->setCallSid($callSid)
                ->setNumber($number)
                ->setFromNumber($phoneNumber)
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

            // create a new ticket for the call
            $ticketMessageCall = new TicketMessageVoicePhoneCall();
            $ticketMessageCall->setPhoneCall($phoneCall);

            $ticketMessage = new TicketMessage();
            $ticketMessage->setPerson($person);
            $ticketMessage->addAttribute($ticketMessageCall);
            $ticketMessage->setMessage('Call from '.$phoneNumber);
            $ticketMessage->setAsAgentNote(true);

            $ticket = new Ticket();
            $ticket->disableAutoTicketProcess();
            $ticket->setSubject('Call from '.$phoneNumber);
            $ticket->setPerson($person);
            $ticket->addMessage($ticketMessage);

            $this->saveTicket($ticket);

            // create twilio new task response
            $twiml = new Twiml();
            $twiml->say(sprintf('Thank you for calling, %s', $this->getHelpdeskName()));

            if ($account->getQueueWorkflowSid()) {
                $this->addTargetResponse($phoneCall, $phoneCall->getNumber()->getTarget(), $twiml);
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
     * @param VoicePhoneCall $phoneCall
     * @param Twiml          $twiml
     */
    private function addConferenceResponse(VoicePhoneCall $phoneCall, Twiml $twiml)
    {
        $account = $phoneCall->getNumber()->getAccount();

        $twiml->dial()->conference($this->getConferenceName($phoneCall), [
            'endConferenceOnExit'           => true,
            'statusCallback'                => $this->getConferenceStatusCallbackUrl($account),
            'statusCallbackMethod'          => 'POST',
            'statusCallbackEvent'           => 'join leave start end mute hold',
            'record'                        => 'record-from-start',
            'recordingStatusCallback'       => $this->getRecordingStatusCallbackUrl($account),
            'recordingStatusCallbackMethod' => 'POST',
        ]);
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
        $em = $this->getManager();

        $number  = $phoneCall->getNumber();
        $account = $number->getAccount();
        $person  = $phoneCall->getPerson();

        $messageAttribute = $this->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            throw new \Exception('Ticket not found');
        }

        $ticket = $messageAttribute->getMessage()->getTicket();

        if ($target instanceof VoiceQueueTarget) {
            $twiml
                ->enqueue([
                    'workflowSid' => $account->getQueueWorkflowSid(),
                ])->task(json_encode([
                    'deskpro_call_id'   => $phoneCall->getId(),
                    'deskpro_queue_id'  => $target->getQueue()->getId(),
                    'deskpro_person_id' => $person ? $person->getId() : null,
                    'deskpro_ticket_id' => $ticket->getId(),
                    'rejected_workers'  => [],
                ]))
            ;
        } elseif ($target instanceof VoiceAgentTarget) {
            $this->addConferenceResponse($phoneCall, $twiml);

            // send agent invite
            $cm = new ClientMessage();
            $cm->setChannel('agent.voice.conference.participant-invite');
            $cm->setForPerson($target->getAgent());
            $cm->setData([
                'number'           => $phoneCall->getFromNumber(),
                'caller_person_id' => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'          => $phoneCall->getId(),
                'conference_sid'   => $phoneCall->getConferenceSid(),
                'ticket_id'        => $ticket->getId(),
            ]);

            $em->persist($cm);
            $em->flush();
        } elseif ($target instanceof VoiceAutoAttendantTarget) {
            $autoAttendant = $target->getAutoAttendant();
            $dialNumbers   = $autoAttendant->getDialNumbers();

            $content = [];
            foreach ($dialNumbers as $dialNumber) {
                $content[] = sprintf(
                    'To call %s, press %d',
                    $dialNumber->getTarget()->getTargetName(), $dialNumber->getDialNum()
                );
            }

            $content[] = 'To repeat the main menu, press * key';
            $content[] = 'To enter agent extension number, press # key';
            $content   = implode('. ', $content);

            $twiml
                ->gather([
                    'numDigits'   => 1,
                    'action'      => $this->getAutoAttendantCallbackUrl($account, $autoAttendant),
                    'finishOnKey' => '',
                ])
                ->say($content)
            ;
        }
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getConferenceCallbackUrl(VoiceAccount $account)
    {
        return $this->get('router')->generate('twilio_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
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
}
