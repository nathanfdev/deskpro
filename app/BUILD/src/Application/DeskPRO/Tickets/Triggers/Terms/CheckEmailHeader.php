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
 * Checks email header value.
 *
 * @option string name
 * @option string value
 */
class CheckEmailHeader extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('name');
        $options->addRequiredNames('value');

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

        $options = $this->getTermOptions();

        $reader  = $context->getEmailContext();
        $header  = $reader->getHeader($options['name']);
        $strings = [];

        if ($header) {
            foreach ($header->getAllParts() as $val) {
                $strings[] = $val;
            }
        }

        $value = TermValue::createWithValue($strings);

        return $this->isStringMatch($ticket, $context, $value, $options['value']);
    }
}
