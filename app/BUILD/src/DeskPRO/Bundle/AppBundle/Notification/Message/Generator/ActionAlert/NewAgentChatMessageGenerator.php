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

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NewAgentChatMessageGenerator.
 */
class NewAgentChatMessageGenerator extends AbstractAgentChatMessageGenerator
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param Serializer            $serializer
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage, Serializer $serializer)
    {
        parent::__construct($em, $tokenStorage);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     *
     * @param NewMessageEvent $event
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            $messages[] = new ActionAlert($target->getId(), $this->getData($event), $event->getName());
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof NewMessageEvent;
    }

    /**
     * @param NewMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getData(NewMessageEvent $event)
    {
        return $this->serializer->toArray(
            new ApiWrapper($this->getChatMessage($event)),
            new SideloadSerializationContext(['agent_chat', 'participants', 'department', 'agent_team'])
        );
    }
}
