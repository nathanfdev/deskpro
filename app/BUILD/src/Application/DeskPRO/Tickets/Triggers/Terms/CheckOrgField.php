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
 * Checks the value of an org field.
 *
 * @option int field_id   The field to check
 * @option mixed value    The value. For choice, this will be multiple ints. For others, it will be a string.
 */
class CheckOrgField extends AbstractCheckCustomField
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('field_id', 'value');
        $options->setAliases('field_id', ['field']);

        return $options;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return array
     */
    public function getCustomDataArray(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($ticket->getOrganization() && $ticket->getOrganization()->getCustomData()) {
            return $ticket->getOrganization()->getCustomData();
        } else {
            return [];
        }
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        return 'CheckOrgField'.$this->getTermOptions()->get('field_id');
    }
}
