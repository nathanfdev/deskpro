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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles active phone call controls panel.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_client/phone_call/{phoneCall}")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceClientPhoneCallController extends AbstractVoiceController
{
    /**
     * Force agent assign to a ticket to open the created voice ticket asap.
     * It works slowly via twilio callbacks.
     *
     * @ApiDoc(
     *     description="Assign agent to the ticket",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/assign_agent")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @return View
     */
    public function assignAgentAction(VoicePhoneCall $phoneCall)
    {
        $agent = $this->getVoiceAgent();
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Agent does not have voice permissions');
        }
        if ($phoneCall->getDateEnded()) {
            throw $this->createBadRequestException('Phone call is already ended');
        }

        $this->cancelForwardingCalls($phoneCall, $this->getUser());
        $ticket = $this->createOrJoinTicketForIncomingCall($phoneCall, $agent);

        return new View($this->wrap($ticket));
    }

    /**
     * @ApiDoc(
     *     description="Returns related phone call ticket",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket"
     * )
     *
     * @Rest\Get("/ticket")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @return View
     */
    public function getPhoneCallTicketAction(VoicePhoneCall $phoneCall)
    {
        $messageAttribute = $this->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            throw $this->createNotFoundException();
        }

        $ticket = $messageAttribute->getMessage()->getTicket();

        return new View($this->wrap($ticket));
    }

    /**
     * @ApiDoc(
     *     description="Toggle agent mute",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     parameters={
     *       {"name"="mute", "description"="is mute enabled", "dataType"="boolean", "required"=false}
     *     }
     * )
     *
     * @Rest\Put("/mute_call")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Request        $request
     *
     * @return View
     */
    public function muteAction(VoicePhoneCall $phoneCall, Request $request)
    {
        $mute        = $request->request->get('mute');
        $participant = $phoneCall->getParticipantByPerson($this->getVoiceAgent());
        if (!$participant) {
            throw $this->createBadRequestException('Phone call participant not found');
        }

        $this->get('twilio_adapter')->muteParticipant($phoneCall, $participant->getCallSid(), $mute);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Toggle the hold status for the end user caller",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     parameters={
     *       {"name"="hold", "description"="put on hold", "dataType"="boolean", "required"=false}
     *     }
     * )
     *
     * @Rest\Put("/hold_call")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Request        $request
     *
     * @return View
     */
    public function holdCallAction(VoicePhoneCall $phoneCall, Request $request)
    {
        $this->toggleHoldConference($phoneCall, $request->request->get('hold'));

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Sends client notification to join the phone call",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/{callType}/{person}/{inviteType}", requirements={"callType"="(add|transfer)", "inviteType"="(cold|warm)"})
     *
     * @param string         $callType
     * @param VoicePhoneCall $phoneCall
     * @param Person         $person
     * @param string         $inviteType
     *
     * @return View
     */
    public function inviteAction($callType, VoicePhoneCall $phoneCall, Person $person, $inviteType)
    {
        if (!$person->getAgentData() || !$person->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();
        if ($callType === 'transfer' && $inviteType === 'cold') {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_COLD_TRANSFER);
            $em->persist($phoneCall);
        }

        // get phone call ticket
        $messageAttribute = $em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            throw $this->createBadRequestException('Unable to get ticket message for the phone call');
        }

        $ticket = $messageAttribute->getMessage()->getTicket();

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-invite',
            [
                'number'           => $phoneCall->getExternalNumber(),
                'caller_person_id' => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'          => $phoneCall->getId(),
                'call_type'        => $callType,
                'conference_sid'   => $phoneCall->getConferenceSid(),
                'from_agent_id'    => $this->getVoiceAgent()->getId(),
                'ticket_id'        => $ticket->getId(),
                'invite_type'      => $inviteType,
                'target'           => $person->getId(),
            ]
        ));

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($person);
        $log->setPhoneCall($phoneCall);
        $log->setDetails([
            'call_type'   => $callType,
            'invite_type' => $inviteType,
            'to_person'   => $person->getId(),
        ]);

        if ($callType === 'transfer') {
            $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_TRANSFER);
        } else {
            $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_INVITED);
        }

        $em->persist($log);
        $em->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Cancel phone call notification",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/cancel_invite/{person}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $person
     *
     * @return View
     */
    public function cancelInviteAction(VoicePhoneCall $phoneCall, Person $person)
    {
        if (!$person->getAgentData() || !$person->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_CANCEL_INVITE);
        $log->setPhoneCall($phoneCall);
        $log->setDetails([
            'to_person' => $person->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-cancel',
            [
                'call_id'  => $phoneCall->getId(),
                'agent_id' => $person->getId(),
                'target'   => $person->getId(),
            ]
        ));

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Ignore phone call notification",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/ignore_invite/{person}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $person
     *
     * @return View
     */
    public function ignoreInviteAction(VoicePhoneCall $phoneCall, Person $person)
    {
        if (!$person->getAgentData() || !$person->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_IGNORE_INVITE);
        $log->setPhoneCall($phoneCall);
        $log->setDetails([
            'from_person' => $person->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-ignore',
            [
                'call_id'  => $phoneCall->getId(),
                'agent_id' => $person->getId(),
            ]
        ));

        // try to end call for cold transfer
        $this->get('twilio_adapter')->tryEndConference($phoneCall);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="End phone call",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/end_call")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @return View
     */
    public function endCallAction(VoicePhoneCall $phoneCall)
    {
        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_HANGUP);
        $log->setPhoneCall($phoneCall);

        $this->getManager()->persist($log);
        $this->getManager()->flush();

        if ($phoneCall->getType() === VoicePhoneCall::DIRECTION_OUTBOUND) {
            // if agent hangup pending call then decline user's call as well
            if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_PENDING) {
                $adapter = $this->get('twilio_adapter');
                foreach ($phoneCall->getUserParticipants() as $participant) {
                    $adapter->cancelCall($phoneCall->getNumber()->getAccount(), $participant->getCallSid());
                }
            }
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    private function toggleHoldConference(VoicePhoneCall $phoneCall, $isHold)
    {
        $this->get('twilio_adapter')->holdConferenceEndUser($phoneCall, $isHold);

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.hold',
            [
                'call_id' => $phoneCall->getId(),
                'hold'    => $isHold,
            ]
        ));
    }

    /**
     * @return Person
     */
    private function getVoiceAgent()
    {
        $agent = $this->getUser();
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Agent does not have voice permissions');
        }

        return $agent;
    }
}
