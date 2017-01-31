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
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     *
     * @param AgentData $agentData
     */
    public function sendVoiceAgentCallsEnabledNotification(AgentData $agentData)
    {
        $cm = new ClientMessage();
        $cm->setChannel('agent.voice.calls_enabled');
        $cm->setData([
            'person_id'           => $agentData->getPerson()->getId(),
            'agent_calls_enabled' => $agentData->isAgentCallsEnabled(),
        ]);

        $this->em->persist($cm);
        $this->em->flush();
    }

    /**
     * Force set 'ticket.use' and 'person.use' permissions that are required to use voice.
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     *
     * @param AgentData $agentData
     */
    public function setRelatedPermissions(AgentData $agentData)
    {
        if (!$agentData->isVoiceEnabled()) {
            return;
        }

        $permission = new Permission();
        $permission->setPerson($agentData->getPerson());
        $permission->setName('agent_people.use');
        $permission->setValue(1);

        $this->em->persist($permission);

        $permission = new Permission();
        $permission->setPerson($agentData->getPerson());
        $permission->setName('agent_tickets.use');
        $permission->setValue(1);

        $this->em->persist($permission);
        $this->em->flush();
    }
}
