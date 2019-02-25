<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\VoicemailHelper;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VoicemailListener.
 */
class VoicemailListener implements EventSubscriberInterface
{
    /**
     * @var
     */
    private $em;

    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var VoiceSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var VoicemailHelper
     */
    private $voicemailHelper;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param VoiceTaskHelper       $taskHelper
     * @param VoiceSettingsResolver $settingsResolver
     * @param TicketManager         $ticketManager
     * @param VoicemailHelper       $voicemailHelper
     */
    public function __construct(
        EntityManager         $em,
        VoiceTaskHelper       $taskHelper,
        VoiceSettingsResolver $settingsResolver,
        TicketManager         $ticketManager,
        VoicemailHelper       $voicemailHelper
    ) {
        $this->em               = $em;
        $this->taskHelper       = $taskHelper;
        $this->settingsResolver = $settingsResolver;
        $this->ticketManager    = $ticketManager;
        $this->voicemailHelper  = $voicemailHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::TIMEOUT => 'onTimeout',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onTimeout(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $phoneCall = $this->taskHelper->getPhoneCall($task);
        if (!$phoneCall) {
            return;
        }

        // mark the phone call as completed (redirected to voicemail)
        $phoneCall->setStatus(VoicePhoneCall::STATUS_VOICEMAIL);
        $this->em->persist($phoneCall);
        $this->em->flush();

        // return redirect response
        $voicemailAsset = null;
        if ($task->getAttribute('queue')) {
            $this->voicemailForQueue($phoneCall, $task);
        } elseif ($task->getAttribute('agent')) {
            $agent = $this->taskHelper->getWorkerAgent($task);
            if ($agent) {
                $this->voicemailHelper->voicemailForAgent($phoneCall, $agent);
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Task           $task
     */
    private function voicemailForQueue(VoicePhoneCall $phoneCall, Task $task)
    {
        $queue = $this->taskHelper->getVoiceQueue($task);
        if (!$queue) {
            return;
        }

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
            $event = ExecutorContext::EVENT_UPDATE;
        }

        $person = $phoneCall->getPerson();
        if ($person && $person->isAgent()) {
            $context = $this->ticketManager->createAgentExecutorContext($person, $event, ExecutorContext::METHOD_API);
        } else {
            $context = $this->ticketManager->createUserExecutorContext($person, $event, ExecutorContext::METHOD_API);
        }

        $this->ticketManager->saveTicket($ticket, $context);
    }
}
