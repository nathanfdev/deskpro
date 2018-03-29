<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Checks if an email was detected as from a robot (autoresponse etc).
 */
class CheckEmailIsRobot extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$context->hasEmailContext()) {
            return false;
        }

        $is_bounce = $context->getVars()->get('is_robot_message', false);

        switch ($this->getTermOperator()) {
            case self::OP_IS:
                return $is_bounce;
            case self::OP_NOT:
                return !$is_bounce;
            default:
                return false;
        }
    }
}
