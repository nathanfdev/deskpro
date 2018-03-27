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
 * Checks the value of a user field.
 *
 * @option int field_id   The field to check
 * @option mixed value    The value. For choice, this will be multiple ints. For others, it will be a string.
 */
class CheckUserField extends AbstractCheckCustomField
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
        if ($ticket->person && $ticket->person->custom_data) {
            return $ticket->person->custom_data;
        } else {
            return [];
        }
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        return 'CheckUserField'.$this->getTermOptions()->get('field_id');
    }
}
