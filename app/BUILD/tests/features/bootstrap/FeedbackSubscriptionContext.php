<?php

namespace DpBehat;

use Application\DeskPRO\Entity\FeedbackSubscription;
use DpBehat\Data\DataContext;

/**
 * Class FeedbackSubscriptionContext.
 */
class FeedbackSubscriptionContext extends BaseContext
{
    /**
     * @Then the :feedbackId feedback should have subscribed persons :personIds
     *
     * @param int    feedbackId
     * @param string personIds
     *
     * @throws \Exception
     */
    public function feedbackHasSubscribedPersons($feedbackId, $personIds)
    {
        $feedbackId = DataContext::replace($feedbackId);
        $personIds  = explode(',', DataContext::replace($personIds));

        $subscribedIds = $this->em()->getRepository(FeedbackSubscription::class)->getSubscribedPersonIds($feedbackId);
        $diff          = array_diff($personIds, $subscribedIds);

        if ($diff) {
            throw new \Exception(sprintf("Can't find feedback subscriptions for persons: [%s]", implode(',', $diff)));
        }
    }
}
