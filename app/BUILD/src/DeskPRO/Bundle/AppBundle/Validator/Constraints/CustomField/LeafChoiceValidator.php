<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class LeafChoiceValidator.
 */
class LeafChoiceValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $item) {
            if (!is_scalar($item)) {
                throw new UnexpectedTypeException($item, 'scalar');
            }
        }

        if (!$constraint instanceof LeafChoice) {
            throw new UnexpectedTypeException($constraint, CustomData::class);
        }

        $customDef = $constraint->customDef;
        if (!$customDef instanceof CustomDefAbstract) {
            throw new UnexpectedTypeException($constraint->customDef, CustomDefAbstract::class);
        }

        $parentNodes = [];
        foreach ($customDef->getChildren() as $child) {
            $parentChoiceId = $child->getOption('parent_id');
            if ($parentChoiceId) {
                $parentNodes[] = $parentChoiceId;
            }
        }

        if (array_intersect($value, $parentNodes)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(LeafChoice::NOT_ASSIGNABLE_CHOICE)
                ->addViolation()
            ;
        }
    }
}
