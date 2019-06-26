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
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
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

        $taskRouter = $this->get('dp.voice.task_router');
        if (!$taskRouter->joinTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            $taskRouter->rejectAnotherWorkerReservation($phoneCall->getTaskSid(), 'agent', $agent->getId());

            throw $this->createBadRequestException('Unable to accept task reservation');
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
        $this->get('dp.voice.event_helper')->sendConferenceStatus($phoneCall);

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
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function holdCallAction(VoicePhoneCall $phoneCall, Request $request)
    {
        $isHold = $request->request->get('hold');

        $this->get('dp.voice.provider_helper')->holdEndUser($phoneCall, $isHold);

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
     * @Rest\Put("/warm_add/{agent}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @throws \Exception
     *
     * @return View
     */
    public function warmAddAction(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        // reserve task for the agent
        $taskRouter = $this->get('dp.voice.task_router');
        if (!$taskRouter->canWorkerAcceptTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Unable to add this agent to the call');
        }
        if (!$taskRouter->reserveAnotherWorkerForTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Unable to reserve task');
        }

        $em = $this->getManager();

        $phoneCall->setStatus(VoicePhoneCall::STATUS_WARM_ADD);
        $phoneCall->setDateWaiting(new \DateTime());
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

        // send invite notification
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
                'phone_call'       => $this->getSerializedPhoneCallData($phoneCall),
                'expire_timeout'   => $this->get('voice_settings_resolver')->getAgentVoicemailTimeout(),
                'target'           => $agent->getId(),
            ]
        ));

        // call forwarding number
        $this->get('dp.voice.provider_helper')->callForwardingNumber($phoneCall, $agent);

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($agent);
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_INVITED);
        $log->setDetails([
            'call_type'   => 'add',
            'invite_type' => 'warm',
            'to_person'   => $agent->getId(),
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

        // reserve task for the agent
        $taskRouter = $this->get('dp.voice.task_router');
        if (!$taskRouter->canWorkerAcceptTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Unable to transfer the call to this agent');
        }
        if (!$taskRouter->reserveAnotherWorkerForTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Unable to reserve task');
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
        $phoneCall->setDateWaiting(new \DateTime());
        $em->flush();

        /** @var Ticket $ticket */
        $ticket = $messageAttribute->getMessage()->getTicket();

        // change ticket assigned agent to follower on cold transfer
        $ticketPerson = $ticket->getAgent();
        if ($ticketPerson) {
            $ticket->setAgent(null);

            $participant = new TicketParticipant();
            $participant->setPerson($ticketPerson);

            $ticket->addParticipant($participant);
        }

        $this->get('dp.voice.ticket_helper')->saveTicket($ticket);
        $this->get('dp.voice.provider_helper')->holdEndUser($phoneCall, true);

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
                'phone_call'       => $this->getSerializedPhoneCallData($phoneCall),
                'expire_timeout'   => $this->get('voice_settings_resolver')->getAgentVoicemailTimeout(),
                'target'           => $agent->getId(),
            ]
        ));

        // call forwarding number
        $this->get('dp.voice.provider_helper')->callForwardingNumber($phoneCall, $agent);

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
     * @Rest\Put("/cold_transfer/agent/{agent}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @throws \Exception
     *
     * @return View
     */
    public function coldTransferToAgentAction(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        // reserve task for the agent
        $taskRouter = $this->get('dp.voice.task_router');
        if (!$taskRouter->canWorkerAcceptTask($phoneCall->getTaskSid(), 'agent', $agent->getId())) {
            throw $this->createBadRequestException('Unable to transfer the call to this agent');
        }

        $em = $this->getManager();

        $phoneCall->setStatus(VoicePhoneCall::STATUS_COLD_TRANSFER);
        $phoneCall->setEnqueuedAs(VoicePhoneCall::ENQUEUED_AS_USER);
        $phoneCall->setDateWaiting(new \DateTime());
        $em->flush();

        // disconnect existing agents from the call
        $this->get('dp.voice.provider_helper')->prepareForColdTransfer($phoneCall);
        $this->get('dp.voice.callbacks_helper')->changeTicketAgentToFollower($phoneCall);

        // create a router task
        // and transfer to call router
        $task = $this->get('dp.voice.task_builder')->createVoiceTransferToAgentTask($phoneCall, $agent, $this->getVoiceAgent());
        $phoneCall->setTaskSid($task->getId());

        $em->flush();

        $this->get('dp.voice.transfer_helper')->transferToTaskRouter($phoneCall);

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
     *     description="Cold transfer call to a voice queue",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/cold_transfer/queue/{queue}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param VoiceQueue     $queue
     *
     * @throws \Exception
     *
     * @return View
     */
    public function coldTransferToQueueAction(VoicePhoneCall $phoneCall, VoiceQueue $queue)
    {
        $em = $this->getManager();

        $phoneCall->setStatus(VoicePhoneCall::STATUS_COLD_TRANSFER);
        $phoneCall->setEnqueuedAs(VoicePhoneCall::ENQUEUED_AS_USER);
        $phoneCall->setDateWaiting(new \DateTime());
        $em->flush();

        $this->get('dp.voice.callbacks_helper')->changeTicketAgentToFollower($phoneCall);

        // create a router task
        // and transfer to call router
        $task = $this->get('dp.voice.task_builder')->createVoiceTransferToQueueTask($phoneCall, $queue);
        $phoneCall->setTaskSid($task->getId());

        $em->flush();

        $this->get('dp.voice.transfer_helper')->transferToTaskRouter($phoneCall);

        // disconnect existing agents from the call
        foreach ($phoneCall->getAgentParticipants() as $participant) {
            $this->get('dp.voice.provider_helper')->kickParticipant($participant);
        }

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_QUEUE_TRANSFER);
        $log->setDetails([
            'call_type'   => 'transfer',
            'invite_type' => 'cold',
            'to_queue'    => $queue->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        return new View($this->wrap($phoneCall));
    }

    /**
     * @ApiDoc(
     *     description="Cold transfer call to a voice auto attendant",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/cold_transfer/auto_attendant/{autoAttendant}")
     *
     * @param VoicePhoneCall     $phoneCall
     * @param VoiceAutoAttendant $autoAttendant
     *
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function coldTransferToAutoAttendantAction(VoicePhoneCall $phoneCall, VoiceAutoAttendant $autoAttendant)
    {
        $em = $this->getManager();

        $phoneCall->setStatus(VoicePhoneCall::STATUS_COLD_TRANSFER);
        $phoneCall->setEnqueuedAs(VoicePhoneCall::ENQUEUED_AS_USER);
        $phoneCall->setDateWaiting(new \DateTime());
        $em->flush();

        $this->get('dp.voice.callbacks_helper')->changeTicketAgentToFollower($phoneCall);

        // transfer to auto attendant
        $this->get('dp.voice.transfer_helper')->transferToAutoAttendant($phoneCall, $autoAttendant);

        // disconnect existing agents from the call
        foreach ($phoneCall->getAgentParticipants() as $participant) {
            $this->get('dp.voice.provider_helper')->kickParticipant($participant);
        }

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPhoneCall($phoneCall);
        $log->setActionType(VoicePhoneCallLog::ACTION_AUTO_ATTENDANT_TRANSFER);
        $log->setDetails([
            'call_type'         => 'transfer',
            'invite_type'       => 'cold',
            'to_auto_attendant' => $autoAttendant->getId(),
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
     * @Rest\Put("/cancel_invite/{agent}")
     *
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param Request        $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function cancelAgentInviteAction(VoicePhoneCall $phoneCall, Person $agent, Request $request)
    {
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $taskRouter = $this->get('dp.voice.task_router');
        $taskRouter->rejectAnotherWorkerReservation($phoneCall->getTaskSid(), 'agent', $agent->getId());

        $em = $this->getManager();

        $phoneCall->setDateWaiting(null);
        if ($phoneCall->isWarmTransfer() || $phoneCall->isWarmAdd()) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);
        }

        $em->flush();

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setPhoneCall($phoneCall);
        $log->setDetails([
            'to_person' => $agent->getId(),
        ]);

        $reason = $request->request->get('reason');
        if ($reason === 'timeout') {
            $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_INVITE_TIMEOUT);
        } else {
            $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_CANCEL_INVITE);
        }

        $em->persist($log);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-cancel',
            [
                'call_id'  => $phoneCall->getId(),
                'agent_id' => $agent->getId(),
                'target'   => $agent->getId(),
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
     * @param Person         $agent
     *
     * @throws \Exception
     *
     * @return View
     */
    public function ignoreInviteAction(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
            throw $this->createBadRequestException('Voice is not enabled for this agent');
        }

        $taskRouter = $this->get('dp.voice.task_router');
        $taskRouter->rejectAnotherWorkerReservation($phoneCall->getTaskSid(), 'agent', $agent->getId());

        $em = $this->getManager();

        $phoneCall->setDateWaiting(null);
        if ($phoneCall->isWarmTransfer() || $phoneCall->isWarmAdd()) {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);
        }

        $em->flush();

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_IGNORE_INVITE);
        $log->setPhoneCall($phoneCall);
        $log->setDetails([
            'from_person' => $agent->getId(),
        ]);

        $em->persist($log);
        $em->flush();

        $this->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
            'agent.voice.conference.participant-ignore',
            [
                'call_id'  => $phoneCall->getId(),
                'agent_id' => $agent->getId(),
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
     * @throws \Exception
     *
     * @return View
     */
    public function endCallAction(VoicePhoneCall $phoneCall)
    {
        $logger = $this->get('dp.voice.logger');
        $logger->info(sprintf(
            '[VoiceClientPhoneCallController] Begin end call callback, call_id = %s',
            $phoneCall->getId()
        ));

        $taskRouter = $this->get('dp.voice.task_router');
        $taskRouter->rejectAnotherWorkerReservation($phoneCall->getTaskSid(), 'agent', $this->getVoiceAgent()->getId());

        if ($phoneCall->getType() === VoicePhoneCall::DIRECTION_OUTBOUND) {
            // if agent hangup pending call then decline user's call as well
            if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_PENDING) {
                $phoneLock = $this->get('dp.voice.phone_lock_helper')->createPhoneLock($phoneCall->getId());

                try {
                    $phoneLock->acquire(true);

                    $logger->info(sprintf(
                        '[VoiceClientPhoneCallController] Lock end call callback to cancel outgoing calls, call_id = %s',
                        $phoneCall->getId()
                    ));

                    $this->getManager()->refresh($phoneCall);

                    $this->get('dp.voice.provider_helper')->endCall($phoneCall);

                    $logger->info(sprintf(
                        '[VoiceClientPhoneCallController] Calls are canceled, call_id = %s, request_ids = %s',
                        $phoneCall->getId(), implode(', ', array_map(function ($sidInfo) {
                            return $sidInfo['callSid'];
                        }, $phoneCall->getCallSids()))
                    ));

                    $phoneCall->setStatus(VoicePhoneCall::STATUS_CANCELED);
                    $this->getManager()->flush();
                } finally {
                    $phoneLock->release();
                }

                $logger->info(sprintf(
                    '[VoiceClientPhoneCallController] End call callback is unlocked, call_id = %s',
                    $phoneCall->getId()
                ));
            }
        }

        // add action log
        $log = new VoicePhoneCallLog();
        $log->setPerson($this->getVoiceAgent());
        $log->setActionType(VoicePhoneCallLog::ACTION_AGENT_HANGUP);
        $log->setPhoneCall($phoneCall);

        $this->getManager()->persist($log);
        $this->getManager()->flush();

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

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return array
     */
    private function getSerializedPhoneCallData(VoicePhoneCall $phoneCall)
    {
        $context = new SideloadSerializationContext();
        $context->setIncludes(['recording_enabled']);
        $context->setInlineSideloads(true);

        $serializedData = $this->get('serializer')->toArray(new ApiWrapper($phoneCall), $context)['data'];
        unset($serializedData['ticket']);

        return $serializedData;
    }
}
