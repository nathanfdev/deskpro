<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;

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
     * Constructor.
     *
     * @param EntityManager           $em
     * @param VoiceSettingsResolver   $voiceSettingsResolver
     * @param TicketManager           $ticketManager
     * @param StorageAdapterInterface $storageAdapter
     * @param VoiceTaskHelper         $voiceTaskHelper
     */
    public function __construct(
        EntityManager           $em,
        VoiceSettingsResolver   $voiceSettingsResolver,
        TicketManager           $ticketManager,
        StorageAdapterInterface $storageAdapter,
        VoiceTaskHelper         $voiceTaskHelper
    ) {
        $this->em                    = $em;
        $this->voiceSettingsResolver = $voiceSettingsResolver;
        $this->ticketManager         = $ticketManager;
        $this->storageAdapter        = $storageAdapter;
        $this->voiceTaskHelper       = $voiceTaskHelper;
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
            // don't create missed tickets for direct agent calls
            && !($task && $task->getAttribute('agent'))
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
                $ticket->setCreationSystem(Ticket::CREATED_PHONE_INBOUND);

                if ($task && $voiceQueue = $this->voiceTaskHelper->getVoiceQueue($task)) {
                    $ticket->setDepartment($voiceQueue->getDepartment());
                    $ticket->setBrand($voiceQueue->getBrand());
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
                }
            }

            $ticket->addMessage($ticketMessage);
            $this->saveTicket($ticket);
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
