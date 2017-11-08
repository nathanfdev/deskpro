<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Repository\VoiceAccountRepository;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceOutboundCallType;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioClientTokens;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;

/**
 * Class VoiceTokenController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_client")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel")
 */
class VoiceClientController extends BaseController
{
    /**
     * @ApiDoc(
     *     description="Returns client voice auth tokens",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioClientTokens"
     * )
     *
     * @Rest\Get("/tokens")
     *
     * @return View
     */
    public function createWorkerTokenAction()
    {
        $adapter = $this->get('twilio_adapter');
        $account = $this->getVoiceAccount();
        $person  = $this->getUser();

        $clientTokens = new TwilioClientTokens(
            $adapter->createWorkerToken($account, $person),
            $adapter->createPhoneToken($account, $person)
        );

        return new View($this->wrap($clientTokens));
    }

    /**
     * @ApiDoc(
     *     description="Returns voice worker activity sids",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioActivities"
     * )
     *
     * @Rest\Get("/activities")
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getActivitiesAction()
    {
        $adapter = $this->get('twilio_adapter');
        $account = $this->getVoiceAccount();

        try {
            return new View($this->wrap($adapter->getActivities($account)));
        } catch (RestException $e) {
            if (in_array($e->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_NOT_FOUND])) {
                return new TwilioActivities('', '', '', '', '');
            }

            throw $e;
        }
    }

    /**
     * @ApiDoc(
     *     description="Declines and ignores incoming phone call",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/reject_call/{taskSid}")
     *
     * @param string $taskSid
     *
     * @return View
     */
    public function rejectCallAction($taskSid)
    {
        // reject task worker
        $adapter = $this->get('twilio_adapter');
        $account = $this->getVoiceAccount();

        $adapter->rejectTaskWorker($account, $taskSid, $this->getUser());

        // log that agent rejected the incoming call
        $em        = $this->getManager();
        $phoneCall = $em->getRepository(VoicePhoneCall::class)->findOneBy([
            'taskSid' => $taskSid,
        ]);

        if ($phoneCall) {
            // add action log
            $log = new VoicePhoneCallLog();
            $log->setPerson($this->getUser());
            $log->setActionType(VoicePhoneCallLog::ACTION_REJECTED);
            $log->setPhoneCall($phoneCall);

            $em->persist($log);
            $em->flush();
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Prepares outbound phone call",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceOutboundCallType"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall"
     * )
     *
     * @Rest\Post("/prepare_outbound_call")
     *
     * @param Request $request
     *
     * @return View
     */
    public function prepareOutboundCallAction(Request $request)
    {
        $form = $this->createForm(VoiceOutboundCallType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $number   = $form->get('call_from')->getData();
        $toNumber = $form->get('call_to')->getData();

        // get the caller person
        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo = $this->getRepository(Person::class);
        $person     = $personRepo->getOrCreateUserByPhoneNumber($toNumber);

        // create phone call
        $phoneCall = new VoicePhoneCall();
        $phoneCall
            ->setNumber($number)
            ->setExternalNumber($toNumber)
            ->setPerson($person)
            ->setType(VoicePhoneCall::DIRECTION_OUTBOUND)
            ->setData([])
        ;

        $em = $this->getManager();
        $em->persist($phoneCall);
        $em->flush();

        return new View($this->wrap($phoneCall));
    }

    /**
     * @return VoiceAccount
     */
    private function getVoiceAccount()
    {
        /** @var VoiceAccountRepository $voiceAccountRepo */
        $voiceAccountRepo = $this->getRepository(VoiceAccount::class);
        $voiceAccount     = $voiceAccountRepo->getVoiceAccount();

        if (!$voiceAccount) {
            throw $this->createBadRequestException('Voice account not found');
        }

        return $voiceAccount;
    }
}
