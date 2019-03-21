<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Handles active phone call controls panel.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_client/phone_call/{phoneCall}")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceClientPhoneCallController extends BaseController
{
    /**
     * Agent accepts a call.
     *
     * @ApiDoc(
     *     description="Agent accepts a call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/accept_call")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     *
     * @return View
     */
    public function acceptCallAction(VoicePhoneCall $phoneCall)
    {
        if ($phoneCall->getDateEnded()) {
            throw $this->createBadRequestException('Phone call is already ended');
        }

        $agent = $this->getVoiceAgent();
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Agent does not have voice permissions');
        }

        $logger = $this->get('dp.voice.logger');
        $start  = microtime(true);

        if (!$this->get('dp.voice.task_router')->acceptTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Phone call is already accepted');
        }

        $logger->info(sprintf('[VoiceClient] Accepting task took %.3fs', microtime(true) - $start));

        $start = microtime(true);
        $this->get('dp.voice.provider_helper')->cancelForwardingCall($phoneCall, $this->getUser());
        $logger->info(sprintf('[VoiceClient] Canceling forwarding calls took %.3fs', microtime(true) - $start));

        $start  = microtime(true);
        $ticket = $this->get('dp.voice.callbacks_helper')->createOrJoinTicketForIncomingCall($phoneCall, $agent);
        $logger->info(sprintf('[VoiceClient] Creating or joining a ticket for the incoming call took %.3fs', microtime(true) - $start));
        $logger->info(sprintf('[VoiceClient] Finishing accepting call (%.3fs)', microtime(true)));

        return new View($this->wrap(['id' => $ticket->getId()]));
    }

    /**
     * Agent rejects a call.
     *
     * @ApiDoc(
     *     description="Agent rejects a call",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/reject_call")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     *
     * @return View
     */
    public function rejectCallAction(VoicePhoneCall $phoneCall)
    {
        if ($phoneCall->getDateEnded()) {
            throw $this->createBadRequestException('Phone call is already ended');
        }

        $agent = $this->getVoiceAgent();
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Agent does not have voice permissions');
        }

        if (!$this->get('dp.voice.task_router')->rejectTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Phone call is already accepted');
        }

        $this->get('dp.voice.provider_helper')->cancelForwardingCall($phoneCall, $this->getUser());

        // if call target is an agent, redirect to voicemail immediately
        $task = $this->container->get('dp.voice.task_router.storage')->getTask($phoneCall->getTaskSid());
        if ($task && $taskAgent = $this->get('dp.voice.voice_task_helper')->getWorkerAgent($task)) {
            $this->get('dp.voice.voicemail_helper')->transferToVoicemail($phoneCall);
        }

        // log that agent rejected the incoming call
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setActionType(VoicePhoneCallLog::ACTION_REJECTED);
        $log->setPhoneCall($phoneCall);

        $this->getManager()->persist($log);
        $this->getManager()->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Force agent assign to a ticket to open the created voice ticket asap.
     * It works slowly in voice callbacks.
     *
     * @ApiDoc(
     *     description="Assign agent to the ticket",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/assign_agent")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
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

        $this->get('dp.voice.provider_helper')->cancelForwardingCall($phoneCall, $this->getUser());
        $ticket = $this->get('dp.voice.callbacks_helper')->createOrJoinTicketForIncomingCall($phoneCall, $agent);

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
     * @throws \Exception
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
     *     description="Verify if user is still connected",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket"
     * )
     *
     * @Rest\Get("/is_active")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @return View
     */
    public function checkIsActiveAction(VoicePhoneCall $phoneCall)
    {
        $start        = microtime(true);
        $isCallActive = $this->get('dp.voice.provider_helper')->isCallActive($phoneCall);
        $this->get('dp.voice.logger')->info(sprintf('[PlivoCallbacks] Checking if call is active took %.3fs', microtime(true) - $start));

        return new View($this->wrap([
            'is_active' => $isCallActive,
        ]));
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
     * @throws \Exception
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

        $this->get('dp.voice.provider_helper')->muteParticipant($phoneCall, $participant->getCallSid(), $mute);

        // log action
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setDetails($request->request->all());
        $log->setPhoneCall($phoneCall);

        if ($mute) {
            $log->setActionType(VoicePhoneCallLog::ACTION_MUTED);
        } else {
            $log->setActionType(VoicePhoneCallLog::ACTION_UNMUTED);
        }

        $em = $this->getManager();
        $em->persist($log);
        $em->flush();

        // send conference status
        $this->get('dp.voice.callbacks_helper')->sendConferenceStatus($phoneCall);

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
        $isHold = $request->request->get('hold');

        $this->get('dp.voice.provider_helper')->holdConferenceEndUser($phoneCall, $isHold);

        // log action
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setDetails($request->request->all());
        $log->setPhoneCall($phoneCall);

        if ($isHold) {
            $log->setActionType(VoicePhoneCallLog::ACTION_HOLD);
        } else {
            $log->setActionType(VoicePhoneCallLog::ACTION_UNHOLD);
        }

        $em = $this->getManager();
        $em->persist($log);
        $em->flush();

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
     * @Rest\Put("/warm_add/{person}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $person
     *
     * @throws \Exception
     *
     * @return View
     */
    public function warmAddAction(VoicePhoneCall $phoneCall, Person $person)
    {
        if (!$person->getAgentData() || !$person->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        // get phone call ticket
        $messageAttribute = $em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            throw $this->createBadRequestException('Unable to get ticket message for the phone call');
        }

        /** @var Ticket $ticket */
        $ticket = $messageAttribute->getMessage()->getTicket();

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-invite',
            [
                'account_id'       => $phoneCall->getNumber()->getAccount()->getId(),
                'number'           => $phoneCall->getExternalNumber(),
                'caller_person_id' => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'          => $phoneCall->getId(),
                'call_type'        => 'add',
                'conference_sid'   => $phoneCall->getConferenceSid(),
                'from_agent_id'    => $this->getVoiceAgent()->getId(),
                'ticket_id'        => $ticket->getId(),
                'invite_type'      => 'warm',
                'target'           => $person->getId(),
            ]
        ));

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($person);
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_INVITED);
        $log->setDetails([
            'call_type'   => 'add',
            'invite_type' => 'warm',
            'to_person'   => $person->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        return new View($this->wrap($phoneCall));
    }

    /**
     * @ApiDoc(
     *     description="Warm transfer call to an another agent",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/warm_transfer/{agent}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @throws \Exception
     *
     * @return View
     */
    public function warmTransferAction(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        // get phone call ticket
        $messageAttribute = $em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            throw $this->createBadRequestException('Unable to get ticket message for the phone call');
        }

        $phoneCall->setStatus(VoicePhoneCall::STATUS_WARM_TRANSFER);
        $em->flush();

        /** @var Ticket $ticket */
        $ticket = $messageAttribute->getMessage()->getTicket();

        // change ticket assigned agent to follower on cold transfer
        $ticketPerson = $ticket->getAgent();
        $ticket->setAgent(null);

        $participant = new TicketParticipant();
        $participant->setPerson($ticketPerson);

        $ticket->addParticipant($participant);
        $this->get('dp.voice.callbacks_helper')->saveTicket($ticket);

        // send transfer notification
        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-invite',
            [
                'account_id'       => $phoneCall->getNumber()->getAccount()->getId(),
                'number'           => $phoneCall->getExternalNumber(),
                'caller_person_id' => $phoneCall->getPerson() ? $phoneCall->getPerson()->getId() : null,
                'call_id'          => $phoneCall->getId(),
                'call_type'        => 'transfer',
                'conference_sid'   => $phoneCall->getConferenceSid(),
                'from_agent_id'    => $this->getVoiceAgent()->getId(),
                'ticket_id'        => $ticket->getId(),
                'invite_type'      => 'warm',
                'target'           => $agent->getId(),
            ]
        ));

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_TRANSFER);
        $log->setDetails([
            'call_type'   => 'transfer',
            'invite_type' => 'warm',
            'to_person'   => $agent->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        return new View($this->wrap($phoneCall));
    }

    /**
     * @ApiDoc(
     *     description="Cold transfer call to an another agent",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/cold_transfer/{agent}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @throws \Exception
     *
     * @return View
     */
    public function coldTransferAction(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        $phoneCall->setStatus(VoicePhoneCall::STATUS_COLD_TRANSFER);
        $em->flush();

        // get phone call ticket
        $messageAttribute = $em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if (!$messageAttribute) {
            throw $this->createBadRequestException('Unable to get ticket message for the phone call');
        }

        /** @var Ticket $ticket */
        $ticket = $messageAttribute->getMessage()->getTicket();

        // change ticket assigned agent to follower on cold transfer
        $ticketPerson = $ticket->getAgent();
        $ticket->setAgent(null);

        $participant = new TicketParticipant();
        $participant->setPerson($ticketPerson);

        $ticket->addParticipant($participant);
        $this->get('dp.voice.callbacks_helper')->saveTicket($ticket);

        // create a router task
        // and transfer to call router
        $task = $this->get('dp.voice.task_builder')->createVoiceTransferTask($phoneCall, $agent, $this->getVoiceAgent());
        $phoneCall->setTaskSid($task->getId());

        $em->flush();

        $account = $phoneCall->getNumber()->getAccount();

        $this->get('dp.voice.provider_helper')->transferCall(
            $phoneCall,
            $this->get('router')->generate($account->getRouterPrefix().'_call_routing_callback', [
                'account'     => $account->getId(),
                'accountAuth' => $account->getAccountAuth(),
                'task'        => $task->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'POST'
        );

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_TRANSFER);
        $log->setDetails([
            'call_type'   => 'transfer',
            'invite_type' => 'cold',
            'to_person'   => $agent->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        return new View($this->wrap($phoneCall));
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
     * @throws \Exception
     *
     * @return View
     */
    public function cancelInviteAction(VoicePhoneCall $phoneCall, Person $person)
    {
        if (!$person->getAgentData() || !$person->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        if ($phoneCall->isWarmTransfer()) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);
            $em->flush();
        }

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

        return new View($this->wrap($phoneCall));
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
     * @throws \Exception
     *
     * @return View
     */
    public function ignoreInviteAction(VoicePhoneCall $phoneCall, Person $person)
    {
        if (!$person->getAgentData() || !$person->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $em = $this->getManager();

        if ($phoneCall->isWarmTransfer()) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);
            $em->flush();
        }

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

        return new View($this->wrap($phoneCall));
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
                $this->get('dp.voice.provider_helper')->cancelCall($phoneCall);
                $this->get('dp.voice.provider_helper')->cancelOutgoingCalls($phoneCall);
            }

            $phoneCall->setStatus(VoicePhoneCall::STATUS_CANCELED);
            $this->getManager()->flush();
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws \Exception
     *
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
