<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class DpDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DpDate) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\DpDate');
        }

        if (!$value instanceof \DateTime) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                return;
            }
        }

        $value = clone $value;
        $value->setTime(0, 0, 0); // force the date to be midnight of that day for comparison

        $this->checkDateRange($value, $constraint);
        $this->checkDaysOfWeek($value, $constraint);
    }

    public function checkDateRange(\DateTime $value, DpDate $constraint)
    {
        if ($constraint->min_date instanceof \DateTime) {
            if ($value < $constraint->min_date) {
                $this->buildViolation($constraint->min_message)
                    ->addViolation();
            }
        }

        if ($constraint->max_date instanceof \DateTime) {
            if ($value > $constraint->max_date) {
                $this->buildViolation($constraint->max_message)
                    ->addViolation();
            }
        }
    }

    public function checkDaysOfWeek(\DateTime $value, DpDate $constraint)
    {
        $day_of_week = (int) $value->format('N');
        --$day_of_week; // we use a 0 index, "N" uses a 1 index

        if (count($constraint->days_of_week)) {
            if (!in_array($day_of_week, $constraint->days_of_week)) {
                $this->buildViolation($constraint->days_message)
                    ->addViolation();
            }
        }
    }
}
