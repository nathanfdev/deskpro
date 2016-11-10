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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Twiml;

/**
 * Class TwilioCallbacksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/twilio_callbacks/{account}/{accountAuth}")
 * @ApiUserContext("open")
 * @Feature("voice")
 */
class TwilioCallbacksController extends BaseController
{
    /**
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

        $query  = $request->query;
        $number = $this->getRepository(VoiceNumber::class)->findOneBy([
            'number' => $query->get('To'),
        ]);

        if (!$number || !$number->getTarget() instanceof VoiceQueueTarget) {
            $twiml = new Twiml();
            $twiml->say(sprintf('Thank you for calling, %s', $this->getHelpdeskName()));
            $twiml->say('Required phone number is out of service.');
        } else {
            $phoneCall = new VoicePhoneCall();
            $phoneCall
                ->setSid($query->get('CallSid'))
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
                    ]))
                ;
            }
        }

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
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

        return new JsonResponse([
            'instruction' => 'dequeue',
            'to'          => TwilioAdapter::getWorkerContactUrl($agent),
        ]);
    }

    /**
     * @return string
     */
    private function getHelpdeskName()
    {
        return $this->getContainer()->getBrandSetting('core.deskpro_name');
    }
}
