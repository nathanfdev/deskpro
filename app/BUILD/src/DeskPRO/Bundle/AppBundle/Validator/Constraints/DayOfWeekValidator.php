<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;

/**
 * Class DayOfWeekValidator.
 */
class DayOfWeekValidator extends ChoiceValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \DateTime) {
            try {
                $value = $this->getDayOfWeek(new \DateTime($value));
            } catch (\Exception $e) {
                $value = -1;
            }
        } else {
            $value = $this->getDayOfWeek($value);
        }

        parent::validate($value, $constraint);
    }

    /**
     * @param \DateTime $value
     *
     * @return int
     */
    protected function getDayOfWeek(\DateTime $value)
    {
        return intval($value->format('N')) - 1;
    }
}
