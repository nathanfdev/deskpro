<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendantDialNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantAgent;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\TaskBuilder;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\TaskRouter;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VoiceCallbacksHelper.
 */
class VoiceCallbacksHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TaskBuilder
     */
    private $taskBuilder;

    /**
     * @var TaskRouter
     */
    private $taskRouter;

    /**
     * @var VoiceSettingsResolver
     */
    private $voiceSettingsResolver;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var VoiceProviderHelper
     */
    private $voiceProviderHelper;

    /**
     * @var WorkerHelper
     */
    private $workerHelper;

    /**
     * @var StorageAdapterInterface
     */
    private $storageAdapter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param Serializer               $serializer
     * @param TaskBuilder              $taskBuilder
     * @param TaskRouter               $taskRouter
     * @param VoiceSettingsResolver    $voiceSettingsResolver
     * @param TicketManager            $ticketManager
     * @param VoiceProviderHelper      $voiceProviderHelper
     * @param WorkerHelper             $workerHelper
     * @param StorageAdapterInterface  $storageAdapter
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager            $em,
        Serializer               $serializer,
        TaskBuilder              $taskBuilder,
        TaskRouter               $taskRouter,
        VoiceSettingsResolver    $voiceSettingsResolver,
        TicketManager            $ticketManager,
        VoiceProviderHelper      $voiceProviderHelper,
        WorkerHelper             $workerHelper,
        StorageAdapterInterface  $storageAdapter,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em                    = $em;
        $this->serializer            = $serializer;
        $this->taskBuilder           = $taskBuilder;
        $this->taskRouter            = $taskRouter;
        $this->voiceSettingsResolver = $voiceSettingsResolver;
        $this->ticketManager         = $ticketManager;
        $this->voiceProviderHelper   = $voiceProviderHelper;
        $this->workerHelper          = $workerHelper;
        $this->storageAdapter        = $storageAdapter;
        $this->dispatcher            = $dispatcher;
    }

    /**
     * @param string $callId
     * @param string $fromNumber
     * @param string $toNumber
     * @param array  $details
     *
     * @throws OutOfServiceException
     *
     * @return VoicePhoneCall
     */
    public function createIncomingPhoneCall($callId, $fromNumber, $toNumber, array $details)
    {
        if (!preg_match('/^\+/', $fromNumber)) {
            $fromNumber = '+'.$fromNumber;
        }
        if (!preg_match('/^\+/', $toNumber)) {
            $toNumber = '+'.$toNumber;
        }

        $number = $this->em->getRepository(VoiceNumber::class)->findOneBy([
            'number' => $toNumber,
        ]);

        if (!$number || !$number->getTarget()) {
            throw new OutOfServiceException();
        }

        // get the caller person
        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo = $this->em->getRepository(Person::class);
        $person     = $personRepo->getOrCreateUserByPhoneNumber($fromNumber);

        // create phone call
        $phoneCall = new VoicePhoneCall();
        $phoneCall
            ->setCallSid($callId)
            ->setNumber($number)
            ->setExternalNumber($fromNumber)
            ->setPerson($person)
            ->setType(VoicePhoneCall::DIRECTION_INBOUND)
            ->setData($details)
        ;

        // create user participant
        $participant = new VoicePhoneCallParticipantUser();
        $participant->setCallSid($callId);
        $participant->setPerson($person);

        $phoneCall->addParticipant($participant);

        // add incoming log
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_NEW_INCOMING);
        $log->setPerson($person);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();

        // log auto-attendant press key event
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_CALL_TARGET);
        $log->setPhoneCall($phoneCall);
        $log->setDetails(array_merge($details, [
            'target' => $phoneCall->getNumber()->getTarget()->getTargetDetails(),
        ]));

        $this->em->persist($log);
        $this->em->flush();

        return $phoneCall;
    }

    /**
     * @param string $callId
     * @param string $agentCallId
     * @param int    $agentId
     * @param array  $details
     * @param string $forwardedNumber
     *
     * @throws OutOfServiceException
     *
     * @return VoicePhoneCall|null
     */
    public function joinIncomingPhoneCall($callId, $agentCallId, $agentId, $forwardedNumber, array $details)
    {
        $agent     = $this->getAgent($agentId);
        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);

        if (!$agent || !$phoneCall) {
            throw new OutOfServiceException();
        }

        if (!$this->voiceProviderHelper->isCallActive($phoneCall)) {
            return;
        }

        // create the agent participant
        $existParticipant = $phoneCall->getParticipantByPerson($agent);
        if ($existParticipant instanceof VoicePhoneCallParticipantAgent) {
            $existParticipant->setCallSid($agentCallId);
            $this->em->flush();
        } else {
            $participant = new VoicePhoneCallParticipantAgent();
            $participant->setCallSid($agentCallId);
            $participant->setPerson($agent);

            $phoneCall->addParticipant($participant);

            $this->em->persist($phoneCall);
            $this->em->flush();
        }

        // log answering event
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setPhoneCall($phoneCall);

        if ($forwardedNumber) {
            $log->setActionType(VoicePhoneCallLog::ACTION_FORWARD_ANSWERED);
            $log->setDetails(array_merge($details, [
                'forwarded_number' => $forwardedNumber,
            ]));
        } else {
            $log->setActionType(VoicePhoneCallLog::ACTION_ANSWERED);
            $log->setDetails($details);
        }

        $this->em->persist($log);
        $this->em->flush();

        return $phoneCall;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     */
    public function rejectIncomingPhoneCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        // reject task worker
        $this->taskRouter->rejectTask($phoneCall->getTaskSid(), 'agent', $agent->getId());

        // log that agent rejected the incoming call
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setActionType(VoicePhoneCallLog::ACTION_REJECTED);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return Ticket
     */
    public function createOrJoinTicketForIncomingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        // create a new ticket for the call
        $messageAttribute = $this->em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            // no ticket is created for the call yet, create and assign agent
            $ticketMessageCall = new TicketMessageVoicePhoneCall();
            $ticketMessageCall->setPhoneCall($phoneCall);

            $ticketMessage = new TicketMessage();
            $ticketMessage->setPerson($phoneCall->getPerson());
            $ticketMessage->addAttribute($ticketMessageCall);
            $ticketMessage->setMessage('Call from '.$phoneCall->getExternalNumber());
            $ticketMessage->setAsAgentNote(true);

            $ticket = new Ticket();
            $ticket->disableAutoTicketProcess();
            $ticket->setSubject('Call from '.$phoneCall->getExternalNumber());
            $ticket->setPerson($phoneCall->getPerson());
            $ticket->setAgent($agent);
            $ticket->setProperty('voice_phone_number', $phoneCall->getExternalNumber());
            $ticket->addMessage($ticketMessage);

            // set ticket department
            $permissionsHelper          = $agent->getHelper('AgentPermissions');
            $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

            $voiceQueue = $phoneCall->getQueue();
            if ($voiceQueue) {
                // set ticket department from the queue
                // make sure the agent has permissions to this department
                $queueDepartment = $voiceQueue->getDepartment();
                if ($queueDepartment && in_array($queueDepartment->getId(), $allowedTicketDepartmentIds)) {
                    $ticket->setDepartment($queueDepartment);
                }
            }

            // set department from the agent
            if (!$ticket->getDepartment()) {
                $departmentId = reset($allowedTicketDepartmentIds);
                if ($departmentId) {
                    $agentDepartment = $this->em->getRepository(Department::class)->find($departmentId);
                    if ($agentDepartment) {
                        $ticket->setDepartment($agentDepartment);
                    }
                }
            }

            $this->saveTicket($ticket);
        } else {
            // ticket is already created, that means we are joining the existing conference
            $ticket = $messageAttribute->getMessage()->getTicket();

            if ($ticket->getAgent()) {
                $participant = new TicketParticipant();
                $participant->setPerson($agent);

                $ticket->addParticipant($participant);
            } else {
                // ticket couldn't have an agent, e.g. on cold transfer
                $ticket->setAgent($agent);
            }

            $this->saveTicket($ticket);
        }

        return $ticket;
    }

    /**
     * @param string $callId
     * @param string $callSid
     * @param string $agentId
     * @param array  $details
     *
     * @throws OutOfServiceException
     */
    public function setOutgoingAgentParticipant($callId, $callSid, $agentId, array $details)
    {
        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw new OutOfServiceException();
        }

        // get the caller person
        $agent = $this->getAgent($agentId);

        $phoneCall->setCallSid($callSid);
        $phoneCall->setData(array_merge($phoneCall->getData(), $details));

        // create agent participant
        $participant = new VoicePhoneCallParticipantAgent();
        $participant->setCallSid($callSid);
        $participant->setPerson($agent);

        $phoneCall->addParticipant($participant);

        // add outgoing log
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_NEW_OUTGOING);
        $log->setPerson($phoneCall->getPerson());
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();
    }

    /**
     * @param string $callId
     * @param string $callSid
     *
     * @throws OutOfServiceException
     */
    public function setOutgoingUserParticipant($callId, $callSid)
    {
        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw new OutOfServiceException();
        }

        if (!$phoneCall->getParticipantByCallSid($callSid)) {
            // create user participant
            $participant = new VoicePhoneCallParticipantUser();
            $participant->setCallSid($callSid);
            $participant->setPerson($phoneCall->getPerson());

            $phoneCall->addParticipant($participant);

            $this->em->persist($phoneCall);
            $this->em->flush();
        }
    }

    /**
     * @param string $callId
     *
     * @throws OutOfServiceException
     */
    public function createTicketForOutgoingPhoneCall($callId)
    {
        if (!$callId) {
            throw new OutOfServiceException();
        }

        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw new OutOfServiceException();
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

        $ticket = null;

        // try to get related ticket from phone call
        $options = $phoneCall->getData();
        if (!empty($options['outgoing_ticket_id'])) {
            $ticket = $this->em->getRepository(Ticket::class)->find($options['outgoing_ticket_id']);
            if ($ticket) {
                $ticket->disableAutoTicketProcess();
            }
        }

        // no ticket found, create a new one
        if (!$ticket) {
            $ticket = new Ticket();
            $ticket->disableAutoTicketProcess();
            $ticket->setSubject('Call to '.$phoneCall->getExternalNumber());
            $ticket->setPerson($phoneCall->getPerson());
            $ticket->setAgent($agent);
            $ticket->setProperty('voice_phone_number', $phoneCall->getExternalNumber());
        }

        $ticket->addMessage($ticketMessage);

        $context = $this->ticketManager->createAgentExecutorContext($agent, ExecutorContext::EVENT_NEW, ExecutorContext::METHOD_API);
        $this->ticketManager->saveTicket($ticket, $context);

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.outgoing-call-answered',
            [
                'ticket_id' => $ticket->getId(),
                'call_sid'  => $agentParticipant->getCallSid(),
                'call_id'   => $callId,
            ]
        ));
    }

    /**
     * @param string $callSid
     * @param array  $details
     */
    public function callHangupByUser($callSid, array $details)
    {
        if (!$callSid) {
            return;
        }

        $participant = $this->em->getRepository(VoicePhoneCallParticipantUser::class)->findOneBy([
            'callSid' => $callSid,
        ]);

        if (!$participant) {
            return;
        }

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $participant->getPhoneCall();

        // set participant leave event time
        $participant->setDateLeft(new \DateTime());
        $this->em->persist($participant);
        $this->em->flush();

        // log end-user ends the call
        $log = new VoicePhoneCallLog();
        $log
            ->setDetails($details)
            ->setPerson($participant->getPerson())
            ->setPhoneCall($participant->getPhoneCall())
            ->setActionType(VoicePhoneCallLog::ACTION_USER_DISCONNECTED)
        ;

        $this->em->persist($log);
        $this->em->flush();

        // ensure that we completed the end-user task if the phone call was not established
        // to avoid new reservation creations
        $this->taskRouter->endTask($phoneCall->getTaskSid());

        // cancel all ringing forwarding calls
        $this->voiceProviderHelper->cancelForwardingCalls($phoneCall);

        // end conference
        $this->voiceProviderHelper->endConference($phoneCall);

        // mark the phone call as finished
        $phoneCall->setDateEnded(new \DateTime());
        if (!$phoneCall->isVoicemail()) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
        }

        // user ends call
        // create a ticket for missed calls
        if (!$phoneCall->hasAgentParticipants()
            // check the call is not answered and voicemail wasn't reached
            // otherwise we got a voicemail record and agent will see it in a separate interface
            && !$phoneCall->isVoicemail()
            // create a ticket just it was assigned to any target
            && $phoneCall->getTaskSid()
            // don't create missed tickets for strange numbers
            && !$phoneCall->isStrangeNumber()
            // no ticket messages were created for this phone call yet
            && !$phoneCall->getTicketMessageAttributes()->count()
        ) {
            $ticketMessageCall = new TicketMessageVoicePhoneCall();
            $ticketMessageCall->setPhoneCall($phoneCall);

            $ticketMessage = new TicketMessage();
            $ticketMessage->setPerson($phoneCall->getPerson());
            $ticketMessage->addAttribute($ticketMessageCall);
            $ticketMessage->setMessage('Missed call from '.$phoneCall->getExternalNumber());
            $ticketMessage->setAsAgentNote(true);

            // try to get last ticket
            $ticket = null;
            if ($this->voiceSettingsResolver->isGroupMissedCallTickets()) {
                /** @var Ticket $lastTicket */
                $lastTicket = $this->em->getRepository(Ticket::class)->getLastTicketForNumber($phoneCall->getExternalNumber());
                if ($lastTicket) {
                    $lastTicket->disableAutoTicketProcess();

                    $now    = new \DateTime();
                    $hours  = $this->voiceSettingsResolver->getGroupMissedCallTicketsTimeout();
                    $offset = clone $lastTicket->getDateCreated();
                    $offset->modify("+{$hours} hours");

                    if ($offset > $now) {
                        $ticket = $lastTicket;
                    }
                }
            }

            // if no last ticket, create a new one
            if (!$ticket) {
                $ticket = new Ticket();
                $ticket->disableAutoTicketProcess();
                $ticket->setSubject('Missed call from '.$phoneCall->getExternalNumber());
                $ticket->setPerson($phoneCall->getPerson());
                $ticket->setProperty('voice_phone_number', $phoneCall->getExternalNumber());
            }

            $ticket->addMessage($ticketMessage);
            $this->saveTicket($ticket);
        }

        // log call end event
        // for now if a end-user finishes the call then it means the conference is ended
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_ENDED);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();
    }

    /**
     * @param string $callSid
     * @param array  $details
     */
    public function callHangupByAgent($callSid, array $details)
    {
        if (!$callSid) {
            return;
        }

        /** @var VoicePhoneCallParticipantAgent $participant */
        $participant = $this->em->getRepository(VoicePhoneCallParticipantAgent::class)->findOneBy([
            'callSid' => $callSid,
        ]);

        if (!$participant) {
            return;
        }

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $participant->getPhoneCall();

        // set participant leave event time
        $participant->setDateLeft(new \DateTime());
        $this->em->persist($participant);
        $this->em->flush();

        // log agent ends the call
        $log = new VoicePhoneCallLog();
        $log
            ->setDetails($details)
            ->setPerson($participant->getPerson())
            ->setPhoneCall($participant->getPhoneCall())
            ->setActionType(VoicePhoneCallLog::ACTION_AGENT_DISCONNECTED)
        ;

        $this->em->persist($log);
        $this->em->flush();

        if (!$phoneCall->isColdTransfer()) {
            $this->voiceProviderHelper->tryEndConference($phoneCall);
        }

        foreach ($phoneCall->getTaskSids() as $taskSid) {
            $this->taskRouter->completeTaskForWorker(
                $taskSid,
                'agent',
                $participant->getPerson()->getId()
            );
        }
    }

    /**
     * @param string $callSid
     * @param array  $details
     */
    public function callBusyByUser($callSid, array $details)
    {
        if (!$callSid) {
            return;
        }

        $participant = $this->em->getRepository(VoicePhoneCallParticipantUser::class)->findOneBy([
            'callSid' => $callSid,
        ]);

        if (!$participant) {
            return;
        }

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $participant->getPhoneCall();

        // set participant leave event time
        $participant->setDateLeft(new \DateTime());
        $this->em->persist($participant);
        $this->em->flush();

        // user declined outgoing call
        // log end-user ends the call
        $log = new VoicePhoneCallLog();
        $log
            ->setDetails($details)
            ->setPerson($participant->getPerson())
            ->setPhoneCall($participant->getPhoneCall())
            ->setActionType(VoicePhoneCallLog::ACTION_USER_DISCONNECTED);

        $this->em->persist($log);
        $this->em->flush();

        // make sure that we completed the end-user task if the phone call was not established
        // to avoid new reservation creations
        // could be empty e.g. for outgoing calls
        if ($phoneCall->getTaskSid()) {
            $task = $this->storageAdapter->getTask($phoneCall->getTaskSid());
            if ($task && $task->isPending()) {
                $task->setStatus(Task::STATUS_CANCELED);
                $this->storageAdapter->saveTask($task);
            }
        }

        // mark the phone call as finished
        $phoneCall->setDateEnded(new \DateTime());
        $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);

        // log call end event
        // for now if a end-user finishes the call then it means the conference is ended
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_ENDED);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.outgoing-call-declined', [
                'CallSid' => $phoneCall->getCallSid(),
            ])
        );
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $callSid
     * @param string         $conferenceSid
     * @param array          $details
     * @param string         $memberId
     */
    public function joinConference(VoicePhoneCall $phoneCall, $callSid, $conferenceSid, array $details, $memberId = null)
    {
        // if didn't get the phone call by conference sid then the initial caller didn't join the conference yet
        // store conference sid on its join callback
        if (!$phoneCall->getConferenceSid()) {
            $phoneCall->setConferenceSid($conferenceSid);
            $this->em->flush();
        }

        $participant = $phoneCall->getParticipantByCallSid($callSid);
        if ($participant) {
            $participant->setMemberId($memberId);
            $this->em->flush();

            if ($participant instanceof VoicePhoneCallParticipantAgent) {
                // set participant join event time
                $participant->setDateJoined(new \DateTime());
                $this->em->flush();

                if ($phoneCall->isColdTransfer()) {
                    // mark the phone call as started
                    $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);

                    $this->em->flush();
                }

                // log participant join event
                $log = new VoicePhoneCallLog();
                $log->setDetails($details);
                $log->setPhoneCall($phoneCall);
                $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_JOINED);
                if ($participant->getPerson()) {
                    $log->setPerson($participant->getPerson());
                }

                $this->em->persist($log);
                $this->em->flush();
            } elseif ($participant instanceof VoicePhoneCallParticipantUser) {
                $this->logConferenceStart($phoneCall, $details);
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function sendConferenceStatus(VoicePhoneCall $phoneCall)
    {
        // send client message
        // for real time ui updates
        $statusParams = [];

        // phone call
        $statusParams['phone_call'] = $this->serializer->toArray($phoneCall, new SideloadSerializationContext());
        unset($statusParams['phone_call']['ticket']);

        // is conference on hold
        $statusParams['hold'] = $phoneCall->getUserParticipants()->count()
            ? $phoneCall->getUserParticipants()->first()->isOnHold()
            : false;

        // all active participants
        $statusParams['agent_participants'] = array_map(function (Person $person) {
            return $person->getId();
        }, $this->voiceProviderHelper->getActivePhoneCallParticipants($phoneCall));

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.conference.status', $statusParams)
        );
    }

    /**
     * @param VoicePhoneCall      $phoneCall
     * @param AbstractVoiceTarget $target
     *
     * @return \DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task|null
     */
    public function createTaskForTarget(VoicePhoneCall $phoneCall, AbstractVoiceTarget $target)
    {
        $person        = $phoneCall->getPerson();
        $relatedPeople = $this->em->getRepository(Person::class)->findByPhoneNumber($phoneCall->getExternalNumber());

        $task = null;

        if ($target instanceof VoiceQueueTarget) {
            $queue = $target->getQueue();
            $task  = $this->taskBuilder->createVoiceTaskForQueue($phoneCall, $queue, $person, $relatedPeople);
            $phoneCall->setTaskSid($task->getId());

            $this->em->persist($phoneCall);
            $this->em->flush();
        } elseif ($target instanceof VoiceAgentTarget) {
            $task = $this->taskBuilder->createVoiceTaskForAgent($phoneCall, $target->getAgent(), $person, $relatedPeople);
            $phoneCall->setTaskSid($task->getId());

            $this->em->persist($phoneCall);
            $this->em->flush();
        }

        return $task;
    }

    /**
     * @param int $agentId
     *
     * @throws \Exception
     *
     * @return Person
     */
    public function getAgent($agentId)
    {
        $agent = $this->em->getRepository(Person::class)->find($agentId);
        if (!$agent || !$agent->isAgent()) {
            throw new OutOfServiceException();
        }
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw new OutOfServiceException();
        }

        return $agent;
    }

    /**
     * @param string $enteredCode
     *
     * @return VoiceAgentTarget|null
     */
    public function getTargetByExtensionNumber($enteredCode)
    {
        $target    = null;
        $agentData = $this->em->getRepository(AgentData::class)->findOneBy([
            'extensionNumber' => $enteredCode,
        ]);

        if ($agentData) {
            $target = new VoiceAgentTarget();
            $target->setAgent($agentData->getPerson());
        }

        return $target;
    }

    /**
     * @param VoicePhoneCall               $phoneCall
     * @param VoiceAutoAttendantDialNumber $dialNumber
     * @param array                        $details
     */
    public function logPressedAutoAttendantDigit(VoicePhoneCall $phoneCall, VoiceAutoAttendantDialNumber $dialNumber, array $details)
    {
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_KEY);
        $log->setDetails(array_merge($details, [
            'target' => $dialNumber->getTarget()->getTargetDetails(),
        ]));
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param array          $details
     */
    public function logPressedUnsupportedAutoAttendantDigit(VoicePhoneCall $phoneCall, array $details)
    {
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_UNSUPPORTED_KEY);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param array          $details
     */
    public function logPressedAutoAttendantRepeatKey(VoicePhoneCall $phoneCall, array $details)
    {
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_REPEAT_KEY);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param array          $details
     */
    public function logPressedAutoAttendantExtensionKey(VoicePhoneCall $phoneCall, array $details)
    {
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_EXTENSION_KEY);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $enteredCode
     * @param array          $details
     */
    public function logEnteredAgentExtension(VoicePhoneCall $phoneCall, $enteredCode, array $details)
    {
        $log = new VoicePhoneCallLog();
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_EXTENSION);
        $log->setDetails(array_merge($details, [
            'entered_code' => $enteredCode,
        ]));

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param array          $details
     */
    public function logConferenceStart(VoicePhoneCall $phoneCall, array $details)
    {
        // mark the phone call as started
        $phoneCall->setDateStarted(new \DateTime());
        $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);

        // log conference start event
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_STARTED);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();
    }

    /**
     * @param Ticket $ticket
     */
    public function saveTicket(Ticket $ticket)
    {
        $changes = $ticket->getStateChangeRecorder();
        if ($changes->isNewTicket()) {
            $event = ExecutorContext::EVENT_NEW;
        } else {
            $event = ExecutorContext::EVENT_UPDATE;
        }

        $person = $ticket->getPerson();
        if ($person && $person->isAgent()) {
            $context = $this->ticketManager->createAgentExecutorContext($person, $event, ExecutorContext::METHOD_API);
        } else {
            $context = $this->ticketManager->createUserExecutorContext($person, $event, ExecutorContext::METHOD_API);
        }

        $this->ticketManager->saveTicket($ticket, $context);
    }
}
