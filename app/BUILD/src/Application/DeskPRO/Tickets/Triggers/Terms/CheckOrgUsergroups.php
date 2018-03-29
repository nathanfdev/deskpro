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
 * Checks if the users belongs to a usergroup.
 *
 * @option int[] usergroup_ids
 */
class CheckOrgUsergroups extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('usergroup_ids');
        $options->addCallbackCheckedOption('usergroup_ids', function ($v) {
            return is_array($v) && !empty($v);
        });

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        return $this->isEntityMatch($ticket, $context, 'organization.usergroups[]', 'id', $options['usergroup_ids']);
    }
}
