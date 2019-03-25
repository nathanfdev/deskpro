<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicemailAgentRecording;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceRecording;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\JobQueue\Processor\VoiceDownloadRecordProcessor;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RecordingDownloadHelper.
 */
class RecordingDownloadHelper
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var JobQueue
     */
    private $jobQueue;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var VoiceSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var VoiceTaskHelper
     */
    private $storage;

    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param Serializer               $serializer
     * @param EventDispatcherInterface $dispatcher
     * @param JobQueue                 $jobQueue
     * @param VoiceSettingsResolver    $settingsResolver
     * @param TicketManager            $ticketManager
     * @param StorageAdapterInterface  $storage
     * @param VoiceTaskHelper          $taskHelper
     */
    public function __construct(
        EntityManager            $em,
        Serializer               $serializer,
        EventDispatcherInterface $dispatcher,
        JobQueue                 $jobQueue,
        VoiceSettingsResolver    $settingsResolver,
        TicketManager            $ticketManager,
        StorageAdapterInterface  $storage,
        VoiceTaskHelper          $taskHelper
    ) {
        $this->em               = $em;
        $this->serializer       = $serializer;
        $this->dispatcher       = $dispatcher;
        $this->jobQueue         = $jobQueue;
        $this->settingsResolver = $settingsResolver;
        $this->ticketManager    = $ticketManager;
        $this->storage          = $storage;
        $this->taskHelper       = $taskHelper;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $recordingUrl
     * @param string         $duration
     */
    public function enqueueRecordingDownload(VoicePhoneCall $phoneCall, $recordingUrl, $duration)
    {
        $recordingEnabled = true;
        if ($phoneCall->getQueue()) {
            $recordingEnabled = $phoneCall->getQueue()->isRecordingEnabled();
        }

        if ($recordingEnabled) {
            $recording = new VoiceRecording();
            $recording->setDuration($duration);
            $recording->setRecordingUrl($recordingUrl);

            $phoneCall->addRecording($recording);

            $this->em->persist($phoneCall);
            $this->em->flush();

            $this->jobQueue->addJob(new Job(VoiceDownloadRecordProcessor::JOB_TYPE, [
                'recording_id' => $recording->getId(),
            ]));
        }

        $serializedData = $this->serializer->toArray(
            new ApiWrapper($phoneCall),
            new SideloadSerializationContext()
        );

        $this->dispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent(
                'agent.voice.recording_status',
                ['data' => $serializedData]
            )
        );
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $recordingUrl
     * @param string         $duration
     */
    public function enqueueVoicemailRecordingDownload(VoicePhoneCall $phoneCall, $recordingUrl, $duration)
    {
        $task = $this->storage->getTask($phoneCall->getTaskSid());
        if (!$task) {
            return;
        }

        // return redirect response
        $voicemailAsset = null;
        if ($task->getAttribute('queue')) {
            $queue = $this->taskHelper->getVoiceQueue($task);
            if ($queue) {
                $this->voicemailForQueue($phoneCall, $queue, $recordingUrl, $duration);
            }
        } elseif ($task->getAttribute('agent')) {
            $agent = $this->taskHelper->getWorkerAgent($task);
            if ($agent) {
                $this->voicemailForAgent($phoneCall, $agent, $recordingUrl, $duration);
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param string         $recordingUrl
     * @param int            $duration
     */
    private function voicemailForAgent(VoicePhoneCall $phoneCall, Person $agent, $recordingUrl, $duration)
    {
        $recording = new VoicemailAgentRecording();
        $recording
            ->setPhoneCall($phoneCall)
            ->setDuration($duration)
            ->setRecordingUrl($recordingUrl)
            ->setAgent($agent)
        ;

        $this->em->persist($recording);
        $this->em->flush();

        $this->jobQueue->addJob(new Job(VoiceDownloadRecordProcessor::JOB_TYPE, [
            'voicemail_recording_id' => $recording->getId(),
        ]));
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param VoiceQueue     $queue
     * @param string         $recordingUrl
     * @param int            $duration
     */
    private function voicemailForQueue(VoicePhoneCall $phoneCall, VoiceQueue $queue, $recordingUrl, $duration)
    {
        $recording = new VoiceRecording();
        $recording->setDuration($duration);
        $recording->setRecordingUrl($recordingUrl);

        $phoneCall->addRecording($recording);

        $this->em->persist($phoneCall);
        $this->em->flush();

        $this->jobQueue->addJob(new Job(VoiceDownloadRecordProcessor::JOB_TYPE, [
            'recording_id' => $recording->getId(),
        ]));

        // create voicemail queue ticket
        $ticketMessageCall = new TicketMessageVoicePhoneCall();
        $ticketMessageCall->setPhoneCall($phoneCall);

        $ticketMessage = new TicketMessage();
        $ticketMessage->setPerson($phoneCall->getPerson());
        $ticketMessage->addAttribute($ticketMessageCall);
        $ticketMessage->setMessage('Call from '.$phoneCall->getExternalNumber());
        $ticketMessage->setAsAgentNote(true);

        // try to get last ticket
        $ticket = null;
        if ($this->settingsResolver->isGroupMissedCallTickets()) {
            /** @var Ticket $lastTicket */
            $lastTicket = $this->em->getRepository(Ticket::class)->getLastTicketForNumber($phoneCall->getExternalNumber());
            if ($lastTicket) {
                $lastTicket->disableAutoTicketProcess();

                $now    = new \DateTime();
                $hours  = $this->settingsResolver->getGroupMissedCallTicketsTimeout();
                $offset = clone $lastTicket->getDateCreated();
                $offset->modify("+{$hours} hours");

                if ($offset > $now) {
                    $ticket = $lastTicket;
                }
            }
        }

        if (!$ticket) {
            $ticket = new Ticket();
            $ticket->disableAutoTicketProcess();
            $ticket->setSubject('Voicemail from '.$phoneCall->getExternalNumber());
            $ticket->setPerson($phoneCall->getPerson());
            $ticket->setProperty('voice_phone_number', $phoneCall->getExternalNumber());
            $ticket->setCreationSystem(Ticket::CREATED_PHONE_INBOUND);

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
        }

        $ticket->addMessage($ticketMessage);

        $changes = $ticket->getStateChangeRecorder();
        if ($changes->isNewTicket()) {
            $event = ExecutorContext::EVENT_NEW;
        } else {
            $event = ExecutorContext::EVENT_REPLY;
        }

        $person = $phoneCall->getPerson();
        if ($person && $person->isAgent()) {
            $context = $this->ticketManager->createAgentExecutorContext($person, $event, ExecutorContext::METHOD_PHONE);
        } else {
            $context = $this->ticketManager->createUserExecutorContext($person, $event, ExecutorContext::METHOD_PHONE);
        }

        $this->ticketManager->saveTicket($ticket, $context);
    }
}
