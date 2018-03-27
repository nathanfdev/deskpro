<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class UniqueCollectionValidator.
 */
class UniqueCollectionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueCollection) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new UnexpectedTypeException($value, 'array or \Traversable');
        }

        /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
        $context  = $this->context;
        $unique   = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($value as $item) {
            if (is_object($item) && $constraint->property) {
                $matched = false;

                foreach ($unique as $uniqueItem) {
                    $matched = true;
                    foreach ((array) $constraint->property as $property) {
                        if ($accessor->getValue($item, $property) !== $accessor->getValue($uniqueItem, $property)) {
                            $matched = false;
                        }
                    }
                    if ($matched) {
                        break;
                    }
                }
            } else {
                $matched = in_array($item, $unique);
            }

            if ($matched) {
                $context
                    ->buildViolation($constraint->message)
                    ->setCode(UniqueCollection::NOT_UNIQUE)
                    ->addViolation()
                ;

                return;
            }

            $unique[] = $item;
        }
    }
}
