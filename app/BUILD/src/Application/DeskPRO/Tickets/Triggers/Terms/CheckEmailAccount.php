<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks email account.
 *
 * @option int[] email_account_ids
 */
class CheckEmailAccount extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('email_account_ids');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        $account_ids = $options['email_account_ids'];

        // Legacy handling where may be a single integer instead of array
        if (!is_array($account_ids)) {
            $account_ids = [$account_ids];
        }

        return $this->isEntityMatch($ticket, $context, 'email_account', 'id', $account_ids);
    }
}
