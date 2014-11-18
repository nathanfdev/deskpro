<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Enable or disable email validation
 *
 * @option bool force
 * @option bool require_validation
 */
class SetRequireValidation extends AbstractContainerAwareAction implements ActionInterface, NoopableInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('force');
        $options->addRequiredNames('require_validation');

        return $options;
    }


    /**
     * {@inheritDoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $email = $ticket->getTicketPersonEmail();
        if (!$email || (!$this->getActionOption('force') && $email->is_own_validated)) {
            return true;
        }

        $email->is_validated = false;
        $email->date_validated = null;

        $ticket->status = 'hidden.validating';

        $this->getContainer()->getEm()->persist($email);
    }


    /**
     * {@inheritDoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $email = $ticket->getTicketPersonEmail();
        if (!$email || (!$this->getActionOption('force') && $email->is_own_validated)) {
            return true;
        }

        return false;
    }
}
