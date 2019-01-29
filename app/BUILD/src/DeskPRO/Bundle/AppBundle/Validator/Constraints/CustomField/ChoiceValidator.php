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
        $isRequired = $constraint->getCustomDefOption('required', true);
        if ($constraint->check_required && $isRequired) {
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
        $minChoices = (int) $constraint->getCustomDefOption('min_length', true);
        $maxChoices = (int) $constraint->getCustomDefOption('max_length', true);

        if (!$isRequired && $minChoices === 1) {
            $validators[] = new Assert\NotBlank();
        }

        $countOptions = [];

        if ($minChoices > 1) {
            $countOptions['min'] = $minChoices;
        }
        if ($maxChoices > 1) {
            $countOptions['max'] = $maxChoices;
        }
        if (!empty($countOptions)) {
            $validators[] = new Assert\Count($countOptions);
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
