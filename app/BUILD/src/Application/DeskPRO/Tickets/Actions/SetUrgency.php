<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the urgency.
 *
 * `mode` can be any of:
 * - set: Sets a specific urgency
 * - add: Adds to urgency
 * - sub: Subtract from urgency
 * - raise: Raises urgency to X if it is lower
 * - lower: Lowers urgency to X if it higher
 *
 * @option int urgency
 * @option int mode
 */
class SetUrgency extends AbstractAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    const MODE_SET   = 'set';
    const MODE_ADD   = 'add';
    const MODE_SUB   = 'sub';
    const MODE_RAISE = 'raise';
    const MODE_LOWER = 'lower';

    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('mode', 'urgency');

        return $options;
    }

    /**
     * @param string $mode
     * @param int    $num
     * @param int    $current_urgency
     *
     * @return int
     */
    private function getUrgencyResult($mode, $num, $current_urgency)
    {
        switch ($mode) {
            case self::MODE_SET:
                return $num;

            case self::MODE_ADD:
                return min(10, $current_urgency + $num);

            case self::MODE_SUB:
                return max(1, $current_urgency - $num);

            case self::MODE_RAISE:
                if ($current_urgency > $num) {
                    return $current_urgency;
                }

                return $num;

            case self::MODE_LOWER:
                if ($current_urgency < $num) {
                    return $current_urgency;
                }

                return $num;
        }

        return $current_urgency;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $target_urgency = $this->getUrgencyResult(
            $this->getActionOption('mode'),
            $this->getActionOption('urgency'),
            $ticket->urgency
        );

        $ticket->urgency = $target_urgency;
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $target_urgency = $this->getUrgencyResult(
            $this->getActionOption('mode'),
            $this->getActionOption('urgency'),
            $ticket->urgency
        );

        if ($ticket->urgency == $target_urgency) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return ['fields'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
