<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class DefaultUserChatQueueFixture.
 */
class DefaultUserChatQueueFixture extends AbstractDpFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $chatQueue = new UserChatQueue();
        $chatQueue->setName('Default');
        $chatQueue->setRoutingModel(UserChatQueue::ROUTING_MODEL_SIMULRING);
        $chatQueue->setIsAllAgents(true);

        $manager->persist($chatQueue);
        $manager->flush();
    }
}
