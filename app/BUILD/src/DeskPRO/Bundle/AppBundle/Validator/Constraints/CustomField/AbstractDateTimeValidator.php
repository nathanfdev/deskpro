<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDataAbstract;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractDateTimeValidator.
 */
abstract class AbstractDateTimeValidator extends AbstractSingleValueValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        if ($constraint->getCustomDefOption('required', true)) {
            $validators[] = new Assert\NotBlank();
        }

        if (!$data) {
            // if no value and value is not required then skip other validators
            return $validators;
        }

        // Format validator
        $validators[] = $this->getFormatValidator();

        // Range validator
        $range_type = $constraint->getCustomDefOption('date_valid_type');

        if ($range_type === 'range') {
            $min_range = (int) $constraint->getCustomDefOption('date_valid_range1');
            $max_range = (int) $constraint->getCustomDefOption('date_valid_range2');

            $min_range_format = $min_range ? ('-'.$min_range.' days') : null;
            $max_range_format = $max_range ? ('+'.$max_range.' days') : null;
        } elseif ($range_type === 'date') {
            $min_range_format = $constraint->getCustomDefOption('date_valid_date1');
            $max_range_format = $constraint->getCustomDefOption('date_valid_date2');
        }

        if (isset($min_range_format)) {
            $min_range_date = new \DateTime($min_range_format);
            $validators[]   = new Assert\GreaterThanOrEqual([
                'value' => $min_range_date->format($this->getFormat()),
            ]);
        }
        if (isset($max_range_format)) {
            $max_range_date = new \DateTime($max_range_format);
            $validators[]   = new Assert\LessThanOrEqual([
                'value' => $max_range_date->format($this->getFormat()),
            ]);
        }

        // Day of week validator
        $valid_dow = $constraint->getCustomDefOption('date_valid_dow');
        if ($valid_dow) {
            $validators[] = new AppAssert\DayOfWeek([
                'choices' => $valid_dow,
            ]);
        }

        return $validators;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDataValue(CustomDataAbstract $custom_data)
    {
        $value = parent::getCustomDataValue($custom_data);

        try {
            $value = new \DateTime('@'.$value);
        } catch (\Exception $e) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
            }
        }

        return $value instanceof \DateTime ? $value->format($this->getFormat()) : $value;
    }

    /**
     * @return Constraint
     */
    abstract protected function getFormatValidator();

    /**
     * @return string
     */
    abstract protected function getFormat();
}
