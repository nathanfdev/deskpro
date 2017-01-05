<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChoiceValidator.
 */
class ChoiceValidator extends AbstractCustomDefConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        $is_required = $constraint->getCustomDefOption('required', true);
        if ($is_required) {
            $validators[] = new Assert\NotBlank();
        }

        if (!$data) {
            // if no value and value is not required then skip other validators
            return $validators;
        }

        // Choices validator
        $validators[] = new Assert\Choice([
            'choices'  => $constraint->custom_def->getChoiceIds(),
            'multiple' => $constraint->getCustomDefOption('multiple', false),
        ]);

        // Count validator
        $min_choices = (int) $constraint->getCustomDefOption('min_length', true);
        $max_choices = (int) $constraint->getCustomDefOption('max_length', true);

        if (!$is_required && $min_choices === 1) {
            $validators[] = new Assert\NotBlank();
        }

        $count_options = [];

        if ($min_choices > 1) {
            $count_options['min'] = $min_choices;
        }
        if ($max_choices > 1) {
            $count_options['max'] = $max_choices;
        }
        if (!empty($count_options)) {
            $validators[] = new Assert\Count($count_options);
        }

        return $validators;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(Collection $value, AbstractCustomDefConstraint $constraint)
    {
        $choices = [];

        /** @var CustomDataAbstract $choice */
        foreach ($value as $choice) {
            $choices[] = $choice->field ? $choice->field->getId() : 0;
        }

        if (!$constraint->getCustomDefOption('multiple', false)) {
            return array_shift($choices);
        }

        return $choices;
    }
}
