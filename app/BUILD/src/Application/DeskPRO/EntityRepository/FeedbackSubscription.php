<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Feedback as FeedbackEntity;

class FeedbackSubscription extends AbstractEntityRepository
{
    /**
     * @param FeedbackEntity|int $feedback
     *
     * @return array
     */
    public function getSubscribedPersonIds($feedback)
    {
        if ($feedback instanceof FeedbackEntity) {
            $feedback = $feedback->id;
        }

        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT IDENTITY(fs.person) FROM DeskPRO:FeedbackSubscription fs WHERE fs.feedback = :feedback'
        );
        $query->setParameter('feedback', $feedback);

        return array_map('current', $query->getResult());
    }
}
