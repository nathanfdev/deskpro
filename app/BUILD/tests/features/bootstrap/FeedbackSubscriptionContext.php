<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
