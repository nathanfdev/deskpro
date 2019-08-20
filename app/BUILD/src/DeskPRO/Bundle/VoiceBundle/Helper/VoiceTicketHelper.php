<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceMissedAgentCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class VoiceTicketHelper.
 */
class VoiceTicketHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var VoiceSettingsResolver
     */
    private $voiceSettingsResolver;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var StorageAdapterInterface
     */
    private $storageAdapter;

    /**
     * @var VoiceTaskHelper
     */
    private $voiceTaskHelper;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager              $em
     * @param VoiceSettingsResolver      $voiceSettingsResolver
     * @param TicketManager              $ticketManager
     * @param StorageAdapterInterface    $storageAdapter
     * @param VoiceTaskHelper            $voiceTaskHelper
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param Serializer                 $serializer
     * @param EventDispatcherInterface   $dispatcher
     */
    public function __construct(
        EntityManager              $em,
        VoiceSettingsResolver      $voiceSettingsResolver,
        TicketManager              $ticketManager,
        StorageAdapterInterface    $storageAdapter,
        VoiceTaskHelper            $voiceTaskHelper,
        BrandAwareSettingsResolver $settingsResolver,
        Serializer                 $serializer,
        EventDispatcherInterface   $dispatcher
    ) {
        $this->em                    = $em;
        $this->voiceSettingsResolver = $voiceSettingsResolver;
        $this->ticketManager         = $ticketManager;
        $this->storageAdapter        = $storageAdapter;
        $this->voiceTaskHelper       = $voiceTaskHelper;
        $this->settingsResolver      = $settingsResolver;
        $this->serializer            = $serializer;
        $this->dispatcher            = $dispatcher;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     */
    public function createMissedTicketMessageIfNotExist(VoicePhoneCall $phoneCall)
    {
        $task = $phoneCall->getTaskSid() ? $this->storageAdapter->getTask($phoneCall->getTaskSid()) : null;

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
            // don't create missed tickets for direct agent calls
            // create direct missed call instead
            if ($task && $task->getAttribute('agent')) {
                $agent = $this->voiceTaskHelper->getWorkerAgent($task);
                if ($agent) {
                    $missedAgentCall = new VoiceMissedAgentCall();
                    $missedAgentCall->setPhoneCall($phoneCall);
                    $missedAgentCall->setAgent($agent);

                    $this->em->persist($missedAgentCall);
                    $this->em->flush();

                    $serializedData = $this->serializer->toArray(
                        new ApiWrapper($missedAgentCall),
                        new SideloadSerializationContext([
                            'voice_phone_call',
                            'person',
                        ])
                    );

                    $this->dispatcher->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent('agent.voice.missed-call', [
                            'data'   => $serializedData,
                            'target' => $agent->getId(),
                        ])
                    );
                }
            } else {
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
                    $hours    = $this->voiceSettingsResolver->getGroupMissedCallTicketsTimeout();
                    $fromDate = new \DateTime("-{$hours} hours");

                    /** @var Ticket $lastTicket */
                    $lastTicket = $this->em->getRepository(Ticket::class)->getLastTicketForNumber($phoneCall->getExternalNumber(), $fromDate);
                    if ($lastTicket) {
                        $lastTicket->disableAutoTicketProcess();
                        $ticket = $lastTicket;
                    }
                }

                // if no last ticket, create a new one
                if (!$ticket) {
                    $ticket = new Ticket();
                    $ticket->disableAutoTicketProcess();
                    $ticket->setSubject('Missed call from '.$phoneCall->getExternalNumber());
                    $ticket->setPerson($phoneCall->getPerson());
                    $ticket->setProperty('voice_phone_number', $phoneCall->getExternalNumber());
                    $ticket->setCreationSystem(Ticket::CREATED_PHONE_INBOUND);

                    if ($task && $voiceQueue = $this->voiceTaskHelper->getVoiceQueue($task)) {
                        $ticket->setDepartment($voiceQueue->getDepartment());
                        $ticket->setBrand($voiceQueue->getBrand());

                        // set asset properties
                        if ($voiceQueue->getVoicemailAgent()) {
                            $ticket->setAgent($voiceQueue->getVoicemailAgent());
                        }
                        if ($voiceQueue->getVoicemailAgentTeam()) {
                            $ticket->setAgentTeam($voiceQueue->getVoicemailAgentTeam());
                        }
                        if ($voiceQueue->getVoicemailDepartment()) {
                            $ticket->setDepartment($voiceQueue->getVoicemailDepartment());
                        }
                    } elseif ($departmentId = $this->voiceSettingsResolver->getAgentDefaultDepartment()) {
                        $department = $this->em->getRepository(Department::class)->find($departmentId);
                        if ($department) {
                            $ticket->setDepartment($department);

                            if ($agentDefaultBrandId = $this->voiceSettingsResolver->getAgentDefaultBrand()) {
                                $agentBrand = $this->em->getRepository(Brand::class)->find($agentDefaultBrandId);
                                if ($agentBrand && $department->hasBrand($agentBrand)) {
                                    $ticket->setBrand($agentBrand);
                                }
                            }
                        }
                    } elseif ($departmentId = $this->settingsResolver->getSetting(DefaultDepartmentSettings::constructName(DefaultDepartmentSettings::DEFAULT_DEPARTMENT_AGENT_TYPE))) {
                        $department = $this->em->getRepository(Department::class)->find($departmentId);
                        if ($department) {
                            $ticket->setDepartment($department);
                        }
                    }
                }

                $ticket->addMessage($ticketMessage);
                $this->saveTicket($ticket);
            }
        }
    }

    /**
     * @param Ticket $ticket
     *
     * @throws \Exception
     */
    public function saveTicket(Ticket $ticket)
    {
        $changes = $ticket->getStateChangeRecorder();
        if ($changes->isNewTicket()) {
            $event = ExecutorContext::EVENT_NEW;
        } else {
            $event = ExecutorContext::EVENT_REPLY;
        }

        $person = $ticket->getPerson();
        if ($person && $person->isAgent()) {
            $context = $this->ticketManager->createAgentExecutorContext($person, $event, ExecutorContext::METHOD_PHONE);
        } else {
            $context = $this->ticketManager->createUserExecutorContext($person, $event, ExecutorContext::METHOD_PHONE);
        }

        $this->ticketManager->saveTicket($ticket, $context);
    }
}
