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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoiceWorkerListener.
 */
class VoiceWorkerListener
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     * @param EntityManager $em
     */
    public function __construct(TwilioAdapter $twilioAdapter, EntityManager $em)
    {
        $this->twilioAdapter = $twilioAdapter;
        $this->em            = $em;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param AgentData $agentData
     */
    public function onCreate(AgentData $agentData)
    {
        if ($agentData->isVoiceEnabled()) {
            $this->createWorker($agentData);
        }
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param AgentData          $agentData
     * @param PreUpdateEventArgs $args
     */
    public function onUpdate(AgentData $agentData, PreUpdateEventArgs $args)
    {
        if (!$args->hasChangedField('isVoiceEnabled')) {
            return;
        }

        if ($agentData->isVoiceEnabled()) {
            $this->createWorker($agentData);
        } else {
            $this->deleteWorker($agentData);
        }
    }

    /**
     * @param AgentData $agentData
     */
    private function createWorker(AgentData $agentData)
    {
        if ($agentData->getVoiceWorkerSid()) {
            return;
        }

        $account = $this->getVoiceAccount();
        if (!$account) {
            return;
        }

        $worker = $this->twilioAdapter->createWorker($account, $agentData->getPerson());
        $agentData->setVoiceWorkerSid($worker->sid);
    }

    /**
     * @param AgentData $agentData
     */
    private function deleteWorker(AgentData $agentData)
    {
        if (!$agentData->getVoiceWorkerSid()) {
            return;
        }

        $account = $this->getVoiceAccount();
        if (!$account) {
            return;
        }

        // delete worker from Twilio
        $this->twilioAdapter->deleteWorker($account, $agentData->getVoiceWorkerSid());

        // unset voice data
        $agentData->setVoiceWorkerSid(null);
        $agentData->setExtensionNumber(null);

        // remove agent from queues
        $this->em->getConnection()->executeQuery('DELETE FROM voice_queue_agents WHERE person_id = :person_id', [
            'person_id' => $agentData->getPerson()->getId(),
        ]);

        // remove specific agent targets
        $this->em->getConnection()->executeQuery('DELETE FROM voice_targets WHERE agent_id = :person_id', [
            'person_id' => $agentData->getPerson()->getId(),
        ]);
    }

    /**
     * @return VoiceAccount|null
     */
    private function getVoiceAccount()
    {
        return $this->em->getRepository(VoiceAccount::class)->getVoiceAccount();
    }
}
