<?php

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Orb\Util\CheckedOptionsArray;

/**
 * Class TicketApproval
 *
 * @package Application\DeskPRO\Tickets\Filters\Terms
 */
class TicketApproval extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames(
            'approval_status'
        );
        $options->addValidNames(
            'approval_template_id',
            'approver_includes_me'
        );

        return $options;
    }
}
