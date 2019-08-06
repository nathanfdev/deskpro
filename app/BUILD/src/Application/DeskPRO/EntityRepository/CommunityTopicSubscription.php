<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\CommunityTopic as CommunityTopicEntity;

class CommunityTopicSubscription extends AbstractEntityRepository
{
    /**
     * @param CommunityTopicEntity|int $topic
     *
     * @return array
     */
    public function getSubscribedPersonIds($topic)
    {
        if ($topic instanceof CommunityTopicEntity) {
            $topic = $topic->getId();
        }

        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT IDENTITY(cts.person) FROM DeskPRO:CommunityTopicSubscription cts WHERE cts.topic = :topic'
        );
        $query->setParameter('topic', $topic);

        return array_map('current', $query->getResult());
    }
}
