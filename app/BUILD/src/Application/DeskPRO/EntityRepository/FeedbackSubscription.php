<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\CommunityTopic as FeedbackEntity;

class FeedbackSubscription extends AbstractEntityRepository
{
    /**
     * @param FeedbackEntity|int $feedback
     *
     * @return array
     */
    public function getSubscribedPersonIds($topic)
    {
        if ($topic instanceof FeedbackEntity) {
            $topic = $topic->id;
        }

        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT IDENTITY(cts.person) FROM DeskPRO:FeedbackSubscription cts WHERE cts.topic = :topic'
        );
        $query->setParameter('topic', $topic);

        return array_map('current', $query->getResult());
    }
}
