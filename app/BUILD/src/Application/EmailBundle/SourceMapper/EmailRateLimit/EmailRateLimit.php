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

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\SourceMapper\EmailRateLimit;

use Application\EmailBundle\EntityRepository\SendmailSourceRepository;

class EmailRateLimit implements EmailRateLimitInterface
{
    private $sourceRepository;

    /**
     * Array of hours => limit which defines the max messages in that time period.
     *
     * For example:
     *      [24 => 50, 336 => 300]
     * Means at most 50 in a day, or 300 in the last 14 days.
     *
     * @var array
     */
    private $limits;

    /**
     * The min time frame to apply limits to. This allows the rate limit
     * time to be 'reset'.
     *
     * @var \DateTime|null
     */
    private $reset_date;

    /**
     * Array of email account IDs that we are applying limits to.
     *
     * @var array|null
     */
    private $in_account_ids;

    /**
     * EmailRateLimit constructor.
     *
     * @param SendmailSourceRepository $sourceRepository
     * @param array                    $limits
     * @param array|null               $in_account_ids
     * @param \DateTime|null           $reset_date
     */
    public function __construct(SendmailSourceRepository $sourceRepository, array $limits, array $in_account_ids = null, \DateTime $reset_date = null)
    {
        $this->sourceRepository = $sourceRepository;
        $this->limits           = $limits;
        $this->reset_date       = $reset_date;
        $this->in_account_ids   = $in_account_ids;
    }

    /**
     * @param array $message The message array (as returned by the SourceMapper)
     *
     * @return bool
     */
    public function isLimited(array $message)
    {
        // Not actually sending
        if ($message['status'] === 'error' || $message['status'] === 'aborted') {
            return false;
        }

        // No limits
        if (!$this->limits) {
            return false;
        }

        // Not applied to this message because not using an account we care about
        if ($this->in_account_ids && !in_array($message['email_account_id'], $this->in_account_ids)) {
            return false;
        }

        foreach ($this->limits as $days => $limit) {
            $date = new \DateTime('-'.$days.' days');
            if ($this->reset_date && $this->reset_date > $date) {
                $date = $this->reset_date;
            }

            $count = $this->sourceRepository->countSendingBetween($date, null, $this->in_account_ids, $limit);
            if ($count > $limit) {
                return true;
            }

            if ($date === $this->reset_date) {
                break;
            }
        }

        return false;
    }
}
