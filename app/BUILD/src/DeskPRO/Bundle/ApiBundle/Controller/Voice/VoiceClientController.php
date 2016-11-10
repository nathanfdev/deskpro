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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Repository\VoiceAccountRepository;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioClientTokens;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

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
     * @Rest\Get("/tokens")
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
     * @Rest\Get("/activities")
     */
    public function getActivitiesAction()
    {
        $adapter = $this->get('twilio_adapter');
        $account = $this->getVoiceAccount();

        return new View($this->wrap($adapter->getActivities($account)));
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
