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
 * Checks cc address for a name.
 *
 * @option string name
 */
class CheckEmailCcName extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('name');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$context->hasEmailContext()) {
            return false;
        }

        $reader  = $context->getEmailContext();
        $strings = [];
        foreach ($reader->getCcAddresses() as $email) {
            $strings[] = $email->getNameUtf8();
        }

        $options = $this->getTermOptions();
        $value   = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['name']);
    }
}
