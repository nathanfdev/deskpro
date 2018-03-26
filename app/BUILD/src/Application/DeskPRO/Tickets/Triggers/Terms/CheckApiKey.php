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
 * Checks if the current context was submitted via the api with a given api key.
 *
 * @option int api_key_id
 */
class CheckApiKey extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('api_key_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();
        $api_key = $context->getVars()->get('via_api_key');
        $id      = $options->get('api_key_id');

        if (!$api_key || !$id) {
            return false;
        }

        if ('not' === $this->getTermOperator() && (int) $id !== (int) $api_key) {
            return true;
        }

        if ('is' === $this->getTermOperator() && (int) $id === (int) $api_key) {
            return true;
        }

        return false;
    }
}
