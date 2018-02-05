<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

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

            $this->saveTicket($ticket);

            // send notification that phone call was answered
            $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                'agent.voice.incoming-call-answered',
                [
                    'deskpro_call_id' => $phoneCall->getId(),
                ]
            ));
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
        $adapter = $this->get('twilio_adapter');
        $account = $phoneCall->getNumber()->getAccount();

        $adapter->rejectTaskWorker($account, $phoneCall->getTaskSid(), $agent);

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
