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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Twiml;

/**
 * Class TwilioCallbacksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/twilio_callbacks/{account}/{accountAuth}")
 * @ApiUserContext("open")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class TwilioCallbacksController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Handle incoming phone call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Get("/phone_number_callback", name="twilio_phone_number_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function phoneNumberCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        if (strpos($request->query->get('From'), 'client:') === 0) {
            // agent connection
            return $this->phoneNumberAgentCallback($account, $request);
        } else {
            // user connection
            return $this->phoneNumberUserCallback($account, $request);
        }
    }

    /**
     * @ApiDoc(
     *     description="Worker assignment callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/assignment_callback", name="twilio_assignment_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function assignmentCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('p')
            ->from(Person::class, 'p')
            ->join('p.agentData', 'a')
            ->where('a.voiceWorkerSid = :worker_sid')
            ->setParameter('worker_sid', $request->request->get('WorkerSid'))
        ;

        /** @var Person $agent */
        $agent = $qb->getQuery()->getOneOrNullResult();
        if (!$agent) {
            throw $this->createBadRequestException('Worker agent not found');
        }

        $task = json_decode($request->request->get('TaskAttributes'), true);
        $url  = $this->get('router')->generate('twilio_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $accountAuth,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($task['deskpro_call_id']);
        $phoneCall->setTaskSid($request->request->get('TaskSid'));

        $this->getManager()->persist($phoneCall);
        $this->getManager()->flush();

        $response = [
            'instruction' => 'redirect',
            'call_sid'    => $task['call_sid'],
            'url'         => $url,
        ];

        return new JsonResponse($response);
    }

    /**
     * @ApiDoc(
     *     description="Create conference callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/conference_callback", name="twilio_conference_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function conferenceCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $callSid   = $request->request->get('CallSid');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['callSid' => $callSid]);
        $statusUrl = $this->get('router')->generate('twilio_conference_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $accountAuth,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }

        $twiml = new Twiml();
        $twiml->dial()->conference($this->getConferenceName($phoneCall), [
            'endConferenceOnExit'  => true,
            'statusCallback'       => $statusUrl,
            'statusCallbackMethod' => 'POST',
            'statusCallbackEvent'  => 'start end join leave',
        ]);

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Conference status callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/conference_status_callback", name="twilio_conference_status_callback")
     *
     * @param VoiceAccount $account
     * @param string       $accountAuth
     * @param Request      $request
     *
     * @return Response
     */
    public function conferenceStatusCallbackAction(VoiceAccount $account, $accountAuth, Request $request)
    {
        if ($account->getAccountAuth() !== $accountAuth) {
            throw $this->createAccessDeniedException();
        }

        $parameters    = $request->request;
        $callSid       = $parameters->get('CallSid');
        $conferenceSid = $parameters->get('ConferenceSid');
        $eventName     = $parameters->get('StatusCallbackEvent');

        $em        = $this->getManager();
        $adapter   = $this->get('twilio_adapter');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['conferenceSid' => $conferenceSid]);

        if ($eventName === 'conference-start') {
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ACTIVE);

            $em->persist($phoneCall);
            $em->flush();
        } elseif ($eventName === 'participant-join') {
            if (!$phoneCall) {
                // store conference sid
                $phoneCall = $this->getRepository(VoicePhoneCall::class)->findOneBy(['callSid' => $callSid]);
                $phoneCall->setConferenceSid($conferenceSid);

                $em->persist($phoneCall);
                $em->flush();
            } else {
                if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_PENDING) {
                    // unhold the conference, could be on cold transfer
                    $adapter->holdConferenceEndUser($phoneCall, false);
                }
            }
        } elseif ($eventName === 'participant-leave') {
            if ($phoneCall->getStatus() === VoicePhoneCall::STATUS_COLD_TRANSFER) {
                // original agent was disconnected, change status to pending
                $phoneCall->setStatus(VoicePhoneCall::STATUS_PENDING);

                $em->persist($phoneCall);
                $em->flush();
            } else {
                $adapter->tryEndConference($phoneCall);
            }
        } elseif ($eventName === 'conference-end') {
            // ensure that we completed the end-user task if the phone call was not established
            // to avoid new reservation creations
            $adapter->endTask($account, $phoneCall->getTaskSid());

            // mark the phone call as finished
            $phoneCall->setStatus(VoicePhoneCall::STATUS_ENDED);

            $em->persist($phoneCall);
            $em->flush();
        }

        // send client message
        $statusParams = $request->request->all();

        if ($phoneCall && in_array($eventName, ['participant-join', 'participant-leave'])) {
            // all active participants
            $statusParams['agent_participants'] = array_map(function (Person $person) {
                return $person->getId();
            }, $adapter->getPhoneCallParticipants($phoneCall));

            // current participant
            $person = $phoneCall->getPersonByCallSid($callSid);
            if ($person) {
                $statusParams['agent_id'] = $person->getId();
            }

            // is conference on hold
            $statusParams['hold'] = $adapter->isConferenceOnHold($phoneCall);
        }

        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.conference.status');
        $cm->setData($statusParams);

        $em->persist($cm);
        $em->flush();
    }

    /**
     * @return string
     */
    private function getHelpdeskName()
    {
        return $this->getContainer()->getBrandSetting('core.deskpro_name');
    }

    /**
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return Response
     */
    private function phoneNumberAgentCallback(VoiceAccount $account, Request $request)
    {
        $callId    = $request->query->get('CallId');
        $phoneCall = $this->getRepository(VoicePhoneCall::class)->find($callId);

        if (!$phoneCall) {
            throw $this->createBadRequestException('Phone call not found');
        }
        if (!$phoneCall->getConferenceSid()) {
            throw $this->createBadRequestException('No conference was created for this phone call');
        }

        $conference = $this->get('twilio_adapter')->getConference($account, $phoneCall->getConferenceSid());
        if ($conference->status === 'completed') {
            $twiml = new Twiml();
            $twiml->hangup();
        } else {
            $agent = $this->getRepository(Person::class)->find($request->query->get('AgentId'));
            if (!$agent || !$agent->isAgent()) {
                throw $this->createBadRequestException('Agent not found');
            }
            if (!$agent->getAgentData() || !$agent->getAgentData()->isVoiceEnabled()) {
                throw $this->createBadRequestException('Agent voice is not enabled');
            }

            $participant = new VoicePhoneCallParticipant();
            $participant->setCallSid($request->query->get('CallSid'));
            $participant->setPerson($agent);

            $phoneCall->addAgentParticipant($participant);

            $this->getManager()->persist($phoneCall);
            $this->getManager()->flush();

            $twiml = new Twiml();
            $twiml->dial()->conference($this->getConferenceName($phoneCall), [
                'beep'    => false,
                'waitUrl' => '',
            ]);
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return Response
     */
    private function phoneNumberUserCallback(VoiceAccount $account, Request $request)
    {
        $query  = $request->query;
        $number = $this->getRepository(VoiceNumber::class)->findOneBy([
            'number'  => $query->get('To'),
            'account' => $account,
        ]);

        if (!$number || !$number->getTarget() instanceof VoiceQueueTarget) {
            $twiml = new Twiml();
            $twiml->say(sprintf('Thank you for calling, %s', $this->getHelpdeskName()));
            $twiml->say('Required phone number is out of service.');
        } else {
            $phoneCall = new VoicePhoneCall();
            $phoneCall
                ->setCallSid($query->get('CallSid'))
                ->setNumber($number)
                ->setFromNumber($query->get('From'))
                ->setData($query->all())
            ;

            $this->getManager()->persist($phoneCall);
            $this->getManager()->flush();

            $twiml = new Twiml();
            $twiml->say(sprintf('Thank you for calling, %s', $this->getHelpdeskName()));

            if ($account->getQueueWorkflowSid()) {
                /** @var VoiceQueueTarget $target */
                $target = $number->getTarget();
                $twiml
                    ->enqueue([
                        'workflowSid' => $account->getQueueWorkflowSid(),
                    ])->task(json_encode([
                        'deskpro_call_id'  => $phoneCall->getId(),
                        'deskpro_queue_id' => $target->getQueue()->getId(),
                        'rejected_workers' => [],
                    ]))
                ;
            }
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return string
     */
    private function getConferenceName(VoicePhoneCall $phoneCall)
    {
        return 'conference'.$phoneCall->getId();
    }
}
