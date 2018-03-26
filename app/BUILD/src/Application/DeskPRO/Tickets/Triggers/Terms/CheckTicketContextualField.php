<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Service\CustomFieldManager;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks the value of a ticket field.
 *
 * @option int field_id   The field to check
 * @option mixed value    The value. For choice, this will be multiple ints. For others, it will be a string.
 */
class CheckTicketContextualField extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('field_id', 'value');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        //------------------------------
        // Get the field value
        //------------------------------
        /* @var CustomFieldManager $manager */
        $value = null;
        if ($manager = $context->getVars()->get('custom_field_manager')) {
            $field_id = $this->getTermOptions()->get('field_id');
            $value    = $manager->getFieldRawData($field_id, $ticket);
        }

        return $this->isStringMatch($ticket, $context, TermValue::createWithValue($value), $options->get('value'));
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        return 'CheckTicketContextualField'.$this->getTermOptions()->get('field_id');
    }
}
