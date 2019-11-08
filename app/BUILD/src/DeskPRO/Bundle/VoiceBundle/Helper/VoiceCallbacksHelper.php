<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendantDialNumber;
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
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Form\Error\ExceptionErrorsGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use DeskPRO\Bundle\VoiceBundle\Exception\OutOfServiceException;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\TaskBuilder;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\TaskRouter;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
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
     * @var VoiceTicketHelper
     */
    private $voiceTicketHelper;

    /**
     * @var TransferCallHelper
     */
    private $transferCallHelper;

    /**
     * @var WorkerHelper
     */
    private $workerHelper;

    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var StorageAdapterInterface
     */
    private $storageAdapter;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var ExceptionErrorsGenerator
     */
    private $errorsGenerator;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManager              $em
     * @param Serializer                 $serializer
     * @param TaskBuilder                $taskBuilder
     * @param TaskRouter                 $taskRouter
     * @param VoiceSettingsResolver      $voiceSettingsResolver
     * @param TicketManager              $ticketManager
     * @param VoiceProviderHelper        $voiceProviderHelper
     * @param VoiceTicketHelper          $voiceTicketHelper
     * @param TransferCallHelper         $transferCallHelper
     * @param WorkerHelper               $workerHelper
     * @param VoiceTaskHelper            $taskHelper
     * @param StorageAdapterInterface    $storageAdapter
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param ExceptionErrorsGenerator   $errorsGenerator
     * @param EventDispatcherInterface   $dispatcher
     * @param LoggerInterface            $logger
     */
    public function __construct(
        EntityManager              $em,
        Serializer                 $serializer,
        TaskBuilder                $taskBuilder,
        TaskRouter                 $taskRouter,
        VoiceSettingsResolver      $voiceSettingsResolver,
        TicketManager              $ticketManager,
        VoiceProviderHelper        $voiceProviderHelper,
        VoiceTicketHelper          $voiceTicketHelper,
        TransferCallHelper         $transferCallHelper,
        WorkerHelper               $workerHelper,
        VoiceTaskHelper            $taskHelper,
        StorageAdapterInterface    $storageAdapter,
        BrandAwareSettingsResolver $settingsResolver,
        ExceptionErrorsGenerator   $errorsGenerator,
        EventDispatcherInterface   $dispatcher,
        LoggerInterface            $logger
    ) {
        $this->em                    = $em;
        $this->serializer            = $serializer;
        $this->taskBuilder           = $taskBuilder;
        $this->taskRouter            = $taskRouter;
        $this->voiceSettingsResolver = $voiceSettingsResolver;
        $this->ticketManager         = $ticketManager;
        $this->voiceProviderHelper   = $voiceProviderHelper;
        $this->voiceTicketHelper     = $voiceTicketHelper;
        $this->transferCallHelper    = $transferCallHelper;
        $this->workerHelper          = $workerHelper;
        $this->taskHelper            = $taskHelper;
        $this->storageAdapter        = $storageAdapter;
        $this->settingsResolver      = $settingsResolver;
        $this->errorsGenerator       = $errorsGenerator;
        $this->dispatcher            = $dispatcher;
        $this->logger                = $logger;
    }

    /**
     * @param string $callSid
     * @param string $fromNumber
     * @param string $toNumber
     * @param array  $details
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws OutOfServiceException
     *
     * @return VoicePhoneCall
     */
    public function createIncomingPhoneCall($callSid, $fromNumber, $toNumber, array $details)
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
        $person     = $personRepo->getOrCreateUserByPhoneNumber($fromNumber, Person::CREATED_PHONE_INBOUND);

        // create phone call
        $phoneCall = new VoicePhoneCall();
        $phoneCall
            ->setCallSid($callSid)
            ->addCallSid($person->getId(), VoicePhoneCall::TYPE_INCOMING, $callSid)
            ->setNumber($number)
            ->setExternalNumber($fromNumber)
            ->setPerson($person)
            ->setType(VoicePhoneCall::DIRECTION_INBOUND)
        ;

        // create user participant
        $participant = new VoicePhoneCallParticipantUser();
        $participant->setCallSid($callSid);
        $participant->setPerson($person);
        $participant->setData($details);

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
     * @param string $agentCallSid
     * @param int    $agentId
     * @param array  $details
     * @param string $forwardedNumber
     *
     * @throws \Exception
     *
     * @return VoicePhoneCall|null
     */
    public function joinIncomingPhoneCall($callId, $agentCallSid, $agentId, $forwardedNumber, array $details)
    {
        $agent     = $this->getAgent($agentId);
        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);

        if (!$agent || !$phoneCall) {
            throw new OutOfServiceException();
        }

        $phoneCall->addCallSid($agentId, VoicePhoneCall::TYPE_INCOMING, $agentCallSid);

        // create the agent participant
        $existParticipant = $phoneCall->getParticipantByPerson($agent);
        if ($existParticipant instanceof VoicePhoneCallParticipantAgent) {
            $existParticipant->setCallSid($agentCallSid);
            $existParticipant->setDateLeft(null);
            $this->em->flush();
        } else {
            $participant = new VoicePhoneCallParticipantAgent();
            $participant->setCallSid($agentCallSid);
            $participant->setPerson($agent);
            $participant->setDateJoined(new \DateTime());
            $participant->setData($details);

            $phoneCall->addParticipant($participant);

            $this->em->persist($phoneCall);
            $this->em->flush();
        }

        // log answering event
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setPhoneCall($phoneCall);

        $task = $this->storageAdapter->getTask($phoneCall->getTaskSid());
        if ($task) {
            $log->setTargetQueue($this->taskHelper->getVoiceQueue($task));
            $log->setTargetAgent($this->taskHelper->getWorkerAgent($task));
        }

        if ($forwardedNumber) {
            $log->setActionType(VoicePhoneCallLog::ACTION_FORWARD_ANSWERED);
            $log->setDetails(array_merge($details, [
                'forwarded_number' => $forwardedNumber,
            ]));
        } else {
            $log->setActionType(VoicePhoneCallLog::ACTION_ANSWERED);
            $log->setDetails($details);
        }

        $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.agent-joined-call',
            [
                'call_id'  => $callId,
                'agent_id' => $agentId,
            ]
        ));

        $this->em->persist($log);
        $this->em->flush();

        return $phoneCall;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function rejectIncomingPhoneCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        // reject task worker
        if ($this->taskRouter->rejectTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            // log that agent rejected the incoming call
            $log = new VoicePhoneCallLog();
            $log->setPerson($agent);
            $log->setActionType(VoicePhoneCallLog::ACTION_REJECTED);
            $log->setPhoneCall($phoneCall);

            $task = $this->storageAdapter->getTask($phoneCall->getTaskSid());
            if ($task) {
                $log->setTargetQueue($this->taskHelper->getVoiceQueue($task));
                $log->setTargetAgent($this->taskHelper->getWorkerAgent($task));
            }

            $this->em->persist($log);
            $this->em->flush();
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @throws \Exception
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
            $ticket->setCreationSystem(Ticket::CREATED_PHONE_INBOUND);

            // set ticket department
            $task = $this->storageAdapter->getTask($phoneCall->getTaskSid());
            if ($task && $queueId = $task->getAttribute('queue')) {
                /** @var VoiceQueue $voiceQueue */
                $voiceQueue = $this->em->getRepository(VoiceQueue::class)->find($queueId);
                if ($voiceQueue) {
                    // set ticket department from the queue
                    // make sure the agent has permissions to this department
                    $permissionsHelper          = $agent->getHelper('AgentPermissions');
                    $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

                    $queueDepartment = $voiceQueue->getDepartment();
                    if ($queueDepartment && in_array($queueDepartment->getId(), $allowedTicketDepartmentIds)) {
                        $ticket->setDepartment($queueDepartment);
                        $ticket->setBrand($voiceQueue->getBrand());
                    }
                }
            }

            // set department from the agent
            $this->setAgentDefaultDepartment($ticket, $agent);

            $this->voiceTicketHelper->saveTicket($ticket);
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

            $this->voiceTicketHelper->saveTicket($ticket);
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
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setOutgoingAgentParticipant($callId, $callSid, $agentId, array $details)
    {
        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw new OutOfServiceException();
        }

        // get the caller person
        $agent = $this->getAgent($agentId);
        $phoneCall->addCallSid($agent->getId(), VoicePhoneCall::TYPE_OUTGOING, $callSid);

        // create agent participant
        $participant = new VoicePhoneCallParticipantAgent();
        $participant->setCallSid($callSid);
        $participant->setPerson($agent);
        $participant->setData($details);

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
     * @param array  $details
     *
     * @throws OutOfServiceException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setOutgoingUserParticipant($callId, $callSid, array $details)
    {
        $phoneCall = $this->em->getRepository(VoicePhoneCall::class)->find($callId);
        if (!$phoneCall) {
            throw new OutOfServiceException();
        }

        $phoneCall->setCallSid($callSid);
        $phoneCall->addCallSid($phoneCall->getPerson()->getId(), VoicePhoneCall::TYPE_OUTGOING, $callSid);

        if (!$phoneCall->getParticipantByCallSid($callSid)) {
            // create user participant
            $participant = new VoicePhoneCallParticipantUser();
            $participant->setCallSid($callSid);
            $participant->setPerson($phoneCall->getPerson());
            $participant->setData($details);

            $phoneCall->addParticipant($participant);
        }

        $this->em->persist($phoneCall);
        $this->em->flush();
    }

    /**
     * @param string $callId
     *
     * @throws \Exception
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
            $ticket->setCreationSystem(Ticket::CREATED_PHONE_OUTBOUND);

            $this->setAgentDefaultDepartment($ticket, $agent);
        }

        $ticket->addMessage($ticketMessage);

        $changes = $ticket->getStateChangeRecorder();
        if ($changes->isNewTicket()) {
            $event = ExecutorContext::EVENT_NEW;
        } else {
            $event = ExecutorContext::EVENT_REPLY;
        }

        $context = $this->ticketManager->createAgentExecutorContext($agent, $event, ExecutorContext::METHOD_PHONE);
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
     *
     * @throws \Exception
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
        $this->logger->info(sprintf(
            '[VoiceCallbacksHelper] Call status = %s, call_id = %s, uuid = %s',
            $phoneCall->getStatus(), $phoneCall->getId(), $callSid
        ));

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
        $this->voiceProviderHelper->endCall($phoneCall);

        // force end all agent workers
        // in case if agent hangup callback is not called for some reason
        foreach ($phoneCall->getTaskSids() as $taskSid) {
            $this->taskRouter->resetWorkersForTask($taskSid);
        }

        // mark the phone call as finished
        if (!$phoneCall->isVoicemail()
            // handle edge case when user reached /voicemail
            // but hanged up the call without leaving a message
            || $phoneCall->getDateWaiting() > new \DateTime('-5 seconds')) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);
        }

        $phoneCall->setDateEnded(new \DateTime());

        // user ends call
        // create a ticket for missed calls
        $this->voiceTicketHelper->createMissedTicketMessageIfNotExist($phoneCall);

        // log call end event
        // for now if a end-user finishes the call then it means the conference is ended
        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_ENDED);
        $log->setDetails($details);
        $log->setPhoneCall($phoneCall);

        $task = $this->storageAdapter->getTask($phoneCall->getTaskSid());
        if ($task) {
            $log->setTargetQueue($this->taskHelper->getVoiceQueue($task));
            $log->setTargetAgent($this->taskHelper->getWorkerAgent($task));
        }

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();
    }

    /**
     * @param string $callSid
     * @param array  $details
     *
     * @throws \Exception
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

        if (!$phoneCall->isColdTransfer() && !$phoneCall->isVoicemail()) {
            if (count($phoneCall->getActiveParticipants()) < 2) {
                $this->voiceProviderHelper->endCall($phoneCall);
            }
        }

        // unhold end-user after attempt to end the conference
        // so there won't be race conditions
        if ($phoneCall->isWarmTransfer()) {
            // mark the phone call as started
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);
            $this->em->flush();

            // unhold end-user
            $this->voiceProviderHelper->holdEndUser($phoneCall, false);
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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

        // force end all agent workers
        // in case if agent hangup callback is not called for some reason
        foreach ($phoneCall->getTaskSids() as $taskSid) {
            $this->taskRouter->resetWorkersForTask($taskSid);
        }

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
                $this->taskRouter->endTask($task->getId());
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

        $task = $this->storageAdapter->getTask($phoneCall->getTaskSid());
        if ($task) {
            $log->setTargetQueue($this->taskHelper->getVoiceQueue($task));
            $log->setTargetAgent($this->taskHelper->getWorkerAgent($task));
        }

        $this->em->persist($log);
        $this->em->persist($phoneCall);
        $this->em->flush();

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('agent.voice.outgoing-call-declined', [
                'call_id'  => $phoneCall->getId(),
                'call_sid' => $phoneCall->getCallSid(),
            ])
        );
    }

    /**
     * @param string $callSid
     * @param array  $details
     */
    public function callFailed($callSid, array $details)
    {
        if (!$callSid) {
            return;
        }

        /** @var AbstractVoicePhoneCallParticipant $participant */
        $participant = $this->em->getRepository(AbstractVoicePhoneCallParticipant::class)->findOneBy([
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
            ->setActionType(VoicePhoneCallLog::ACTION_FAILED)
        ;

        $this->em->persist($log);
        $this->em->flush();

        if ($phoneCall->isOutgoingCall() && $participant instanceof VoicePhoneCallParticipantUser) {
            // mark the phone call as finished
            $phoneCall->setDateEnded(new \DateTime());
            $phoneCall->setStatus(VoicePhoneCall::STATUS_FAILED);

            $this->em->flush();
            $this->taskRouter->endTask($phoneCall->getTaskSid());

            $errorMessage = $this->errorsGenerator->generateByErrorCode(ErrorsCodes::VOICE_INVALID_NUMBER, [], ['call_to']);
            $this->dispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent.voice.outgoing-provider-error', [
                    'call_id' => $phoneCall->getId(),
                    'errors'  => $errorMessage,
                ])
            );
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $callSid
     * @param string         $conferenceSid
     * @param string         $memberId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function joinConference(VoicePhoneCall $phoneCall, $callSid, $conferenceSid, $memberId = null)
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
        }
    }

    /**
     * @param VoicePhoneCall      $phoneCall
     * @param AbstractVoiceTarget $target
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     */
    public function changeTicketAgentToFollower(VoicePhoneCall $phoneCall)
    {
        // get phone call ticket
        $messageAttribute = $this->em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute instanceof TicketMessageVoicePhoneCall) {
            return;
        }

        /** @var Ticket $ticket */
        $ticket = $messageAttribute->getMessage()->getTicket();

        // change ticket assigned agent to follower on cold transfer
        $ticketAgent = $ticket->getAgent();
        $ticket->setAgent(null);

        if ($ticketAgent) {
            $participant = new TicketParticipant();
            $participant->setPerson($ticketAgent);

            $ticket->addParticipant($participant);
        }

        $this->voiceTicketHelper->saveTicket($ticket);
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function logPressedAutoAttendantDigit(VoicePhoneCall $phoneCall, VoiceAutoAttendantDialNumber $dialNumber, array $details)
    {
        $target = $dialNumber->getTarget();

        $log = new VoicePhoneCallLog();
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_PRESS_KEY);
        $log->setDetails(array_merge($details, [
            'target' => $target->getTargetDetails(),
        ]));

        if ($target instanceof VoiceQueueTarget) {
            $log->setTargetQueue($target->getQueue());
        } elseif ($target instanceof VoiceAgentTarget) {
            $log->setTargetAgent($target->getAgent());
        } elseif ($target instanceof VoiceAutoAttendantTarget) {
            $log->setTargetAutoAttendant($target->getAutoAttendant());
        }

        $log->setPhoneCall($phoneCall);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param array          $details
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
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
     * @param Person $agent
     */
    private function setAgentDefaultDepartment(Ticket $ticket, Person $agent)
    {
        // set department from the agent
        if (!$ticket->getDepartment()) {
            $permissionsHelper          = $agent->getHelper('AgentPermissions');
            $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

            $voiceAgentDefaultDepartmentId = $this->voiceSettingsResolver->getAgentDefaultDepartment();
            $defaultDepartmentId           = $this->settingsResolver->getSetting(DefaultDepartmentSettings::constructName(DefaultDepartmentSettings::DEFAULT_DEPARTMENT_AGENT_TYPE));
            if ($voiceAgentDefaultDepartmentId && in_array($voiceAgentDefaultDepartmentId, $allowedTicketDepartmentIds)) {
                $departmentId = $voiceAgentDefaultDepartmentId;
            } elseif ($defaultDepartmentId && in_array($defaultDepartmentId, $allowedTicketDepartmentIds)) {
                $departmentId = $defaultDepartmentId;
            } else {
                $departmentId = reset($allowedTicketDepartmentIds);
            }

            if ($departmentId) {
                $agentDepartment = $this->em->getRepository(Department::class)->find($departmentId);
                if ($agentDepartment) {
                    $ticket->setDepartment($agentDepartment);

                    if ($voiceAgentDefaultBrandId = $this->voiceSettingsResolver->getAgentDefaultBrand()) {
                        $agentBrand = $this->em->getRepository(Brand::class)->find($voiceAgentDefaultBrandId);
                        if ($agentBrand && $agentDepartment->hasBrand($agentBrand)) {
                            $ticket->setBrand($agentBrand);
                        }
                    }
                }
            }
        }
    }
}
