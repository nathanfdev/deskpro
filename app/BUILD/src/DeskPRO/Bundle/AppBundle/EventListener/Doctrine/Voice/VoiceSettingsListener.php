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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Permission;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoiceSettingsListener.
 */
class VoiceSettingsListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Send notification to real-time update voice agents online count.
     *
     * @ORM\PreFlush()
     *
     * @param AgentData $agentData
     */
    public function sendVoiceAgentCallsEnabledNotification(AgentData $agentData)
    {
        $agent = $agentData->getPerson();
        if (!$agent) {
            return;
        }

        $uow = $this->em->getUnitOfWork();

        $originalAgentData = $uow->getOriginalEntityData($agentData);
        if (!empty($originalAgentData) && $originalAgentData['agentCallsEnabled'] === $agentData->isAgentCallsEnabled()) {
            return;
        }

        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.calls_enabled');
        $cm->setData([
            'person_id'           => $agent->getId(),
            'agent_calls_enabled' => $agentData->isAgentCallsEnabled(),
        ]);

        $this->em->persist($cm);
        $uow->computeChangeSet($this->em->getClassMetadata(get_class($cm)), $cm);
    }

    /**
     * Force set 'ticket.use' and 'person.use' permissions that are required to use voice.
     *
     * @ORM\PreFlush()
     *
     * @param AgentData $agentData
     */
    public function setRelatedPermissions(AgentData $agentData)
    {
        if (!$agentData->isVoiceEnabled()) {
            return;
        }

        $agent = $agentData->getPerson();
        if (!$agent) {
            return;
        }

        foreach (['agent_people.use', 'agent_tickets.use'] as $permName) {
            if (!$agent->hasPerm($permName)) {
                $permission = new Permission();
                $permission->setPerson($agent);
                $permission->setName($permName);
                $permission->setValue(1);

                $this->em->persist($permission);

                $uow = $this->em->getUnitOfWork();
                $uow->computeChangeSet($this->em->getClassMetadata(get_class($permission)), $permission);
            }
        }
    }
}
