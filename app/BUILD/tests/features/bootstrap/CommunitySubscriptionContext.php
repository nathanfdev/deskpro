<?php

namespace DpBehat;

use Application\DeskPRO\Entity\CommunityTopicSubscription;
use DpBehat\Data\DataContext;

/**
 * Class CommunitySubscriptionContext.
 */
class CommunitySubscriptionContext extends BaseContext
{
    /**
     * @Then the :topicId community topic should have subscribed persons :personIds
     *
     * @param int    $topicId
     * @param string $personIds
     *
     * @throws \Exception
     */
    public function topicHasSubscribedPersons($topicId, $personIds)
    {
        $topicId   = DataContext::replace($topicId);
        $personIds = explode(',', DataContext::replace($personIds));

        $subscribedIds = $this->em()->getRepository(CommunityTopicSubscription::class)->getSubscribedPersonIds($topicId);
        $diff          = array_diff($personIds, $subscribedIds);

        if ($diff) {
            throw new \Exception(sprintf("Can't find community topic subscriptions for persons: [%s]", implode(',', $diff)));
        }
    }
}
