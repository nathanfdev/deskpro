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
 * Checks if now is a day of week.
 *
 * `days` is an int where 1=Monday, 7=Sunday (ISO 8601).
 *
 * @option int[] days   The days to test for
 * @option string tz    The timezone to test in
 * @option string var   The value date to test (defaults to now). Also available: 'date_created'
 * @option \DateTime test_date  When specified, this date is used instead of now
 */
class CheckDayOfWeek extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('days', 'tz');
        $options->addValidNames('var', 'test_date');
        $options->addCallbackCheckedOption('var', function ($v) {
            return $v == 'now' || $v == 'date_created' || !$v;
        });

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $op      = $this->getTermOperator();
        $options = $this->getTermOptions();

        try {
            $tz = new \DateTimeZone($options->get('tz', 'UTC'));
        } catch (\Exception $e) {
            $context->getLogger()->warn("[CheckDayOfWeek] Invalid timezone: {$options->get('tz')}");

            return false;
        }

        if ($options->has('test_date')) {
            $now = $options->get('test_date');
        } else {
            $var = $options->get('var', 'now');
            switch ($var) {
                case 'now':
                    $now = new \DateTime('now', $tz);
                    break;
                case 'date_created':
                    $now = $ticket->date_created ?: new \DateTime('now');
                    $now->setTimezone($tz);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown var type: $var");
            }
        }

        $days = $options->get('days');
        if (!is_array($days)) {
            $days = [$days];
        }
        $days = array_map(function ($d) {
            return (int) $d;
        }, $days);

        $is_match = in_array((int) $now->format('N'), $days);

        if ($is_match) {
            if ($op == 'is') {
                return true;
            } else {
                return false;
            }
        } else {
            if ($op == 'is') {
                return false;
            } else {
                return false;
            }
        }
    }
}
