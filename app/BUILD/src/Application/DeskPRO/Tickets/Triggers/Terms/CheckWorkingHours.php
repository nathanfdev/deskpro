<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\Arrays;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\OptionsArray;
use Orb\Util\WorkHoursSet;

/**
 * Checks when ticket was created.
 *
 * @option int date1
 * @option int date2
 * @option string date1_relative
 * @option string date2_relative
 */
class CheckWorkingHours extends AbstractTriggerTerm
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames('set_name');
        $options->addValidNames('working_hours');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getTermOptions();

        if ($options->get('set_name') == 'default') {
            $context->getLogger()->debug('[CheckWorkingHours] Default hours');
            //todo refactor terms so they can get passed a container
            $working_hours = App::getSetting('core_tickets.work_hours');
            if ($working_hours && !is_array($working_hours)) {
                $working_hours = @unserialize($working_hours);
            }
        } else {
            $context->getLogger()->debug('[CheckWorkingHours] Custom hours');
            $working_hours = $options->get('working_hours');
        }

        if (!$working_hours) {
            return false;
        }

        $working_hours = Arrays::removeEmptyArray($working_hours);
        $working_hours = Arrays::removeNull($working_hours);
        $working_hours = Arrays::removeEmptyString($working_hours);

        $working_hours = new OptionsArray($working_hours);
        $wh            = new WorkHoursSet(
            $working_hours->get('start_hour', 9) * 3600 + $working_hours->get('start_min', 0) * 60,
            $working_hours->get('end_hour', 18) * 3600 + $working_hours->get('end_min', 0) * 60,
            $working_hours->get('work_days', [1, 2, 3, 4, 5]),
            $working_hours->get('timezone', $working_hours->get('timezone', 'UTC')),
            $working_hours->get('holidays', [])
        );

        $context->getLogger()->debug('[CheckWorkingHours] Config: '.Arrays::implodeTemplate($working_hours->all(), '{KEY}: {VAL}, '));

        try {
            $tz = new \DateTimeZone($working_hours->get('timezone', 'UTC'));
            if (!$tz) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        $now = new \DateTime('now', $tz);

        $is_in_workday = $wh->isInWorkDay($now);
        if ($is_in_workday) {
            $context->getLogger()->debug('[CheckWorkingHours] IS in working hours');
        } else {
            $context->getLogger()->debug('[CheckWorkingHours] IS NOT in working hours');
        }

        if ('is' === $this->getTermOperator() && $is_in_workday) {
            return true;
        }

        if ('not' === $this->getTermOperator() && !$is_in_workday) {
            return true;
        }

        return false;
    }
}
