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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DateTimeValidator.
 */
class DateTimeValidator extends AbstractSingleValueValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators(AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        if ($constraint instanceof Date) {
            $validators[] = new Assert\Date();
        } elseif ($constraint instanceof DateTime) {
            $validators[] = new Assert\DateTime();
        }

        // Required validator
        if ($constraint->getCustomDefOption('required')) {
            $validators[] = new Assert\NotBlank();
        }

        // Range validator
        $range_type = $constraint->getCustomDefOption('date_valid_type');
        $min_range  = $constraint->getCustomDefOption('date_valid_range1');
        $max_range  = $constraint->getCustomDefOption('date_valid_range2');

        $in_days = $range_type === 'range';

        if ($min_range) {
            $validators[] = new Assert\GreaterThanOrEqual([
                'value' => $in_days ? '-'.(int) $min_range.' days' : $min_range,
            ]);
        }

        if ($max_range) {
            $validators[] = new Assert\LessThanOrEqual([
                'value' => $in_days ? '+'.(int) $max_range.' days' : $max_range,
            ]);
        }

        return $validators;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDataValue(CustomDataAbstract $custom_data)
    {
        return $custom_data->getInput();
    }
}
