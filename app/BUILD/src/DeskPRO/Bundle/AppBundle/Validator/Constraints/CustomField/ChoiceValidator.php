<?php

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

        // Leaf nodes
        $validators[] = new LeafChoice([
            'customDef' => $constraint->custom_def,
        ]);

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
            $choices[] = $choice->getField() ? $choice->getField()->getId() : 0;
        }

        if (!$constraint->getCustomDefOption('multiple', false)) {
            return array_shift($choices);
        }

        return $choices;
    }
}
