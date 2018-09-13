<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;

/**
 * Class AbstractVoiceController.
 */
abstract class AbstractVoiceController extends BaseController
{
    use TicketSaveTrait;

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return Ticket
     */
    protected function createOrJoinTicketForIncomingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        // create a new ticket for the call
        $messageAttribute = $this->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
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
                    $agentDepartment = $this->getRepository(Department::class)->find($departmentId);
                    if ($agentDepartment) {
                        $ticket->setDepartment($agentDepartment);
                    }
                }
            }

            $this->saveTicket($ticket);
        } else {
            // ticket is already created, that means we are joining the existing conference
            $ticket = $messageAttribute->getMessage()->getTicket();

            $participant = new TicketParticipant();
            $participant->setPerson($agent);

            $ticket->addParticipant($participant);
            $this->saveTicket($ticket);
        }

        return $ticket;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     */
    protected function rejectIncomingPhoneCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        // reject task worker
        $this->get('dp.voice.task_router')->rejectTask($phoneCall->getTaskSid(), 'agent', $agent->getId());

        // log that agent rejected the incoming call
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getUser());
        $log->setActionType(VoicePhoneCallLog::ACTION_REJECTED);
        $log->setPhoneCall($phoneCall);

        $em = $this->getManager();
        $em->persist($log);
        $em->flush();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     */
    protected function cancelForwardingCalls(VoicePhoneCall $phoneCall, Person $agent)
    {
        $adapter = $this->get('twilio_adapter');
        $account = $phoneCall->getNumber()->getAccount();

        $adapter->cancelForwardingCall($account, $agent);
    }
}
