<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoiceClientPhoneCallController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_client/phone_call/{phoneCall}")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceClientPhoneCallController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Toggle the hold status for the end user caller",
     *     statusCodes={
     *         204="Returned if everything is ok"
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
     *     }
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

        if ($callType === 'transfer' && $inviteType === 'cold') {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_COLD_TRANSFER);
            $this->getManager()->persist($phoneCall);
        }

        // send agent invite
        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.conference.participant-invite');
        $cm->setForPerson($person);
        $cm->setData([
            'number'         => $phoneCall->getFromNumber(),
            'call_id'        => $phoneCall->getId(),
            'call_type'      => $callType,
            'conference_sid' => $phoneCall->getConferenceSid(),
            'from_agent_id'  => $this->getUser()->getId(),
            'invite_type'    => $inviteType,
        ]);

        $this->getManager()->persist($cm);
        $this->getManager()->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Cancel phone call notification",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     }
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

        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.conference.participant-cancel');
        $cm->setForPerson($person);
        $cm->setData([
            'call_id'  => $phoneCall->getId(),
            'agent_id' => $person->getId(),
        ]);

        $this->getManager()->persist($cm);
        $this->getManager()->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Ignore phone call notification",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     }
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

        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.conference.participant-ignore');
        $cm->setData([
            'call_id'  => $phoneCall->getId(),
            'agent_id' => $person->getId(),
        ]);

        $this->getManager()->persist($cm);
        $this->getManager()->flush();

        // try to end call for cold transfer
        $this->get('twilio_adapter')->tryEndConference($phoneCall);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="End phone call",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     }
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
        $this->get('twilio_adapter')->tryEndConference($phoneCall);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    private function toggleHoldConference(VoicePhoneCall $phoneCall, $isHold)
    {
        $this->get('twilio_adapter')->holdConferenceEndUser($phoneCall, $isHold);

        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.conference.hold');
        $cm->setData([
            'call_id' => $phoneCall->getId(),
            'hold'    => $isHold,
        ]);

        $this->getManager()->persist($cm);
        $this->getManager()->flush();
    }
}
